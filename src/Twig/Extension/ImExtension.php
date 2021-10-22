<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Twig\Extension;

use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Twig\TokenParser\Imresize as ImResizeTokenParser;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ImExtension extends AbstractExtension
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getTokenParsers()
    {
        return [
            new ImResizeTokenParser(),
        ];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getFilters()
    {
        return [
            new TwigFilter('imresize', [$this, 'imResize'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('imresize', [$this, 'imResize'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }

    /**
     * Called by the compile method to replace the image sources with image cache sources.
     *
     * @param string $html
     *
     * @return string
     */
    public function convert($html)
    {
        preg_match_all('|<img ([^>]+)>|', $html, $matches);

        foreach ($matches[0] as $img) {
            $crawler = new Crawler();
            $crawler->addContent($img);
            $imgTag = $crawler->filter('img');

            $src = $imgTag->attr('src');
            $width = $imgTag->attr('width');
            $height = $imgTag->attr('height');

            if (!empty($width) || !empty($height)) {
                $format = $width . 'x' . $height;
                $updatedTagString = preg_replace("| src=[\"']" . $src . "[\"']|", ' src="' . $this->imResize($src, $format) . '"', $img);
                $html = str_replace($img, $updatedTagString, $html);
            }
        }

        return $html;
    }

    /**
     * Returns the cached path, after executing the asset twig function.
     *
     * @param string $path   Path of the source file
     * @param string $format Imbundle format string
     *
     * @return mixed
     */
    public function imResize($path, $format)
    {
        // Remove extra whitespaces
        $path = trim($path);

        $separator = '';
        // Transform absolute url to custom url like : http/ or https/ or simply /
        if (0 === strpos($path, 'http://') || 0 === strpos($path, 'https://') || 0 === strpos($path, '//')) {
            $path = str_replace(['://', '//'], '/', $path);
        } elseif (0 === strpos($path, '/')) {
            // If the path started with a slash, we will add it at the start of the path result
            $separator = '/';
        }

        // Remove the first slash, as we add it manually
        $path = ltrim($path, '/');

        return $separator . $this->manager->getCachePath() . '/' . $format . '/' . $path;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'leapt_im';
    }
}
