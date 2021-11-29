<?php

declare(strict_types=1);

namespace Leapt\ImBundle;

use Leapt\ImBundle\Exception\InvalidArgumentException;
use Leapt\ImBundle\Exception\NotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Manager
{
    protected string $projectDir;
    protected string $publicPath;
    protected string $cachePath;

    public function __construct(
        protected Wrapper $wrapper,
        string $projectDir,
        string $publicPath,
        string $cachePath,
        protected array $formats = [],
    ) {
        $this->setProjectDir($projectDir);
        $this->setPublicPath($publicPath);
        $this->setCachePath($cachePath);
    }

    /**
     * @param array<string, string> $config
     */
    public function addFormat(string $name, array $config): void
    {
        $this->formats[$name] = $config;
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function setProjectDir(string $projectDir): void
    {
        $this->projectDir = rtrim($projectDir, '/');
    }

    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): void
    {
        $this->publicPath = trim($publicPath, '/');
    }

    public function getPublicDirectory(): string
    {
        return $this->getProjectDir() . '/' . $this->getPublicPath();
    }

    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    public function setCachePath(string $cachePath): void
    {
        $this->cachePath = trim($cachePath, '/');
    }

    public function getCacheDirectory(): string
    {
        return $this->getProjectDir() . '/' . $this->getPublicPath() . '/' . $this->getCachePath();
    }

    /**
     * To know if a cache exist for an image in a format.
     */
    public function cacheExists(string $format, string $path): bool
    {
        return true === file_exists($this->getCacheDirectory() . '/' . $format . '/' . $path);
    }

    /**
     * To get a cached image content.
     */
    public function getCacheContent(string $format, string $path): string
    {
        return file_get_contents($this->getCacheDirectory() . '/' . $format . '/' . $path);
    }

    /**
     * To get the web path for a format.
     */
    public function getUrl(string $format, string $path): string
    {
        return $this->getCachePath() . '/' . $format . '/' . $path;
    }

    /**
     * Shortcut to run a "convert" command => creates a new image.
     */
    public function convert(string|array $format, string $file): string
    {
        $file = ltrim($file, '/');
        $this->checkImage($file);

        return $this->wrapper->run('convert', $this->getPublicDirectory() . '/' . $file, $this->convertFormat($format), $this->getCacheDirectory() . '/' . $this->pathify($format) . '/' . $file);
    }

    /**
     * Shortcut to run a "mogrify" command => modifies the image source.
     */
    public function mogrify(string|array $format, string $file): string
    {
        $this->checkImage($file);

        return $this->wrapper->run('mogrify', $file, $this->convertFormat($format));
    }

    /**
     * The cached path is equivalent to the original path except that the '://' syntax after the protocol is replaced by a simple "/", to conserve a correct URL encoded string.
     * The Twig tag 'imResize' will automatically make this conversion for you.
     */
    public function downloadExternalImage(string $format, string $path): string
    {
        $protocol = substr($path, 0, strpos($path, '/'));
        $newPath = str_replace($protocol . '/', $this->getCacheDirectory() . '/' . $format . '/' . $protocol . '/', $path);

        $this->wrapper->checkDirectory($newPath);

        $fp = fopen($newPath, 'w');

        $ch = curl_init(str_replace($protocol . '/', $protocol . '://', $path));
        curl_setopt($ch, \CURLOPT_FILE, $fp);
        curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, \CURLOPT_HEADER, 0);

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $newPath;
    }

    /**
     * Returns the attributes for converting the image regarding a specific format.
     *
     * @return array<string>
     */
    private function convertFormat(mixed $format): array
    {
        if (\is_array($format)) {
            // sounds like the format is already done, let's keep it as it is
            return $format;
        }
        if (\array_key_exists($format, $this->formats)) {
            // it's a format defined in config, let's use all defined parameters
            return $this->formats[$format];
        }

        if (preg_match('/^([0-9]*)x([0-9]*)/', $format)) {
            // it's a custom [width]x[height] format, let's make a thumb
            return ['thumbnail' => $format];
        }

        throw new InvalidArgumentException(sprintf('Unknown IM format: %s', $format));
    }

    /**
     * Validates that an image exists.
     */
    private function checkImage(string $path): void
    {
        if (!file_exists($this->getPublicDirectory() . '/' . $path) && !file_exists($path)) {
            throw new NotFoundException(sprintf('Unable to find the image "%s" to cache', $path));
        }

        if (!is_file($this->getPublicDirectory() . '/' . $path) && !is_file($path)) {
            throw new HttpException(400, sprintf('[ImBundle] "%s" is no file', $path));
        }
    }

    /**
     * Takes a format (array or string) and return it as a valid path string.
     */
    private function pathify(mixed $format): string
    {
        if (\is_array($format)) {
            return md5(serialize($format));
        }

        return $format;
    }
}
