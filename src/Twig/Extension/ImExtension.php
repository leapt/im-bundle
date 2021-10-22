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
    public function __construct(private Manager $manager)
    {
    }

    public function getTokenParsers(): array
    {
        return [
            new ImResizeTokenParser(),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('imresize', [$this, 'imResize'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('imresize', [$this, 'imResize'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }

    /**
     * Called by the compile method to replace the image sources with image cache sources.
     */
    public function convert(string $html): string
    {
        preg_match_all('|<img ([^>]+)>|', $html, $matches);

        foreach ($matches[0] as $img) {
            $crawler = new Crawler();
            $crawler->addContent($img);
            $imgTag = $crawler->filter('img');

            $src = $imgTag->attr('src');
            \assert(\is_string($src));
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
     */
    public function imResize(string $path, string $format): string
    {
        // Remove extra whitespaces
        $path = trim($path);

        $separator = '';
        // Transform absolute url to custom url like : http/ or https/ or simply /
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            $path = str_replace(['://', '//'], '/', $path);
        } elseif (str_starts_with($path, '/')) {
            // If the path started with a slash, we will add it at the start of the path result
            $separator = '/';
        }

        // Remove the first slash, as we add it manually
        $path = ltrim($path, '/');

        return $separator . $this->manager->getCachePath() . '/' . $format . '/' . $path;
    }

    public function getName(): string
    {
        return 'leapt_im';
    }
}
