<?php

namespace Leapt\ImBundle;

use Leapt\ImBundle\Exception\InvalidArgumentException;
use Leapt\ImBundle\Exception\NotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Im manager.
 */
class Manager
{
    /**
     * @var Wrapper
     */
    protected $wrapper;

    /**
     * @var array
     */
    protected $formats;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var string
     */
    protected $publicPath;

    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @param Wrapper $wrapper    The ImBundle Wrapper instance
     * @param string  $projectDir Symfony project root directory
     * @param string  $publicPath Relative path to the public folder (relative to project directory)
     * @param string  $cachePath  Relative path to the images cache folder (relative to public path)
     * @param array   $formats    Formats definition
     */
    public function __construct(Wrapper $wrapper, $projectDir, $publicPath, $cachePath, $formats = [])
    {
        $this->wrapper = $wrapper;
        $this->formats = $formats;
        $this->setProjectDir($projectDir);
        $this->setPublicPath($publicPath);
        $this->setCachePath($cachePath);
    }

    /**
     * Add a format to the config.
     *
     * @param string $name
     * @param string $config
     */
    public function addFormat($name, $config)
    {
        $this->formats[$name] = $config;
    }

    /**
     * @return string
     */
    public function getProjectDir()
    {
        return $this->projectDir;
    }

    /**
     * @param string $projectDir
     */
    public function setProjectDir($projectDir)
    {
        $this->projectDir = rtrim($projectDir, '/');
    }

    /**
     * @return string
     */
    public function getPublicPath()
    {
        return $this->publicPath;
    }

    /**
     * @param string $publicPath
     */
    public function setPublicPath($publicPath)
    {
        $this->publicPath = trim($publicPath, '/');
    }

    /**
     * @return string
     */
    public function getWebDirectory()
    {
        return $this->getProjectDir() . '/' . $this->getPublicPath();
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * @param string $cachePath
     */
    public function setCachePath($cachePath)
    {
        $this->cachePath = trim($cachePath, '/');
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->getProjectDir() . '/' . $this->getPublicPath() . '/' . $this->getCachePath();
    }

    /**
     * To know if a cache exist for a image in a format.
     *
     * @param string $format ImBundle format string
     * @param string $path   Source file path
     *
     * @return bool
     */
    public function cacheExists($format, $path)
    {
        return true === file_exists($this->getCacheDirectory() . '/' . $format . '/' . $path);
    }

    /**
     * To get a cached image content.
     *
     * @param string $format ImBundle format string
     * @param string $path   Source file path
     *
     * @return string
     */
    public function getCacheContent($format, $path)
    {
        return file_get_contents($this->getCacheDirectory() . '/' . $format . '/' . $path);
    }

    /**
     * To get the web path for a format.
     *
     * @param string $format ImBundle format string
     * @param string $path   Source file path
     *
     * @return string
     */
    public function getUrl($format, $path)
    {
        return $this->getCachePath() . '/' . $format . '/' . $path;
    }

    /**
     * Shortcut to run a "convert" command => creates a new image.
     *
     * @param string $format ImBundle format string
     * @param string $file   Source file path
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function convert($format, $file)
    {
        $file = ltrim($file, '/');
        $this->checkImage($file);

        return $this->wrapper->run('convert', $this->getWebDirectory() . '/' . $file, $this->convertFormat($format), $this->getCacheDirectory() . '/' . $this->pathify($format) . '/' . $file);
    }

    /**
     * Shortcut to run a "mogrify" command => modifies the image source.
     *
     * @param string $format ImBundle format string
     * @param string $file   Source file path
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function mogrify($format, $file)
    {
        $this->checkImage($file);

        return $this->wrapper->run('mogrify', $file, $this->convertFormat($format));
    }

    /**
     * @param string $format ImBundle format string
     * @param string $path   cached path for an external image - ex: http/somepath/somefile.jpg or https/somepath/someotherfile.jpg
     *
     * The cached path is equivalent to the original path except that the '://' syntax after the protocol is replaced by a simple "/", to conserve a correct URL encoded string
     * The Twig tag 'imResize' will automatically make this conversion for you
     *
     * @return string
     */
    public function downloadExternalImage($format, $path)
    {
        $protocol = substr($path, 0, strpos($path, '/'));
        $newPath = str_replace($protocol . '/', $this->getCacheDirectory() . '/' . $format . '/' . $protocol . '/', $path);

        $this->wrapper->checkDirectory($newPath);

        $fp = fopen($newPath, 'w');

        $ch = curl_init(str_replace($protocol . '/', $protocol . '://', $path));
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $newPath;
    }

    /**
     * Returns the attributes for converting the image regarding a specific format.
     *
     * @param string $format
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function convertFormat($format)
    {
        if (\is_array($format)) {
            // sounds like the format is already done, let's keep it as it is
            return $format;
        }
        if (\array_key_exists($format, $this->formats)) {
            // it's a format defined in config, let's use all defined parameters
            return $this->formats[$format];
        } elseif (preg_match('/^([0-9]*)x([0-9]*)/', $format)) {
            // it's a custom [width]x[height] format, let's make a thumb
            return ['thumbnail' => $format];
        }
        throw new InvalidArgumentException(sprintf('Unknown IM format: %s', $format));
    }

    /**
     * Validates that an image exists.
     *
     * @param string $path
     *
     * @throws NotFoundException
     * @throws HttpException
     */
    private function checkImage($path)
    {
        if (!file_exists($this->getWebDirectory() . '/' . $path) && !file_exists($path)) {
            throw new NotFoundException(sprintf('Unable to find the image "%s" to cache', $path));
        }

        if (!is_file($this->getWebDirectory() . '/' . $path) && !is_file($path)) {
            throw new HttpException(400, sprintf('[ImBundle] "%s" is no file', $path));
        }
    }

    /**
     * Takes a format (array or string) and return it as a valid path string.
     *
     * @param mixed $format
     *
     * @return string
     */
    private function pathify($format)
    {
        if (\is_array($format)) {
            return md5(serialize($format));
        }

        return $format;
    }
}
