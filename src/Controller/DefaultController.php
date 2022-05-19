<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Controller;

use Leapt\ImBundle\Exception\RuntimeException;
use Leapt\ImBundle\Manager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    /**
     * Main action: renders the image cache and returns it to the browser.
     *
     * @param string $format A format name defined in config or a string [width]x[height]
     * @param string $path   The path of the source file (@see Manager::downloadExternalImage for more info on external/remote images)
     */
    public function index(Manager $im, Request $request, string $format, string $path): Response
    {
        if (str_starts_with($path, 'http/') || str_starts_with($path, 'https/')) {
            $newPath = $im->downloadExternalImage($format, $path);
            $im->mogrify($format, $newPath);
        } else {
            $im->convert($format, $path);
        }

        if (!$im->cacheExists($format, $path)) {
            throw new RuntimeException(sprintf('Caching of image failed for %s in %s format', $path, $format));
        }

        $extension = pathinfo($path, \PATHINFO_EXTENSION);
        $contentType = $request->getMimeType($extension);
        if (empty($contentType)) {
            $contentType = 'image/' . $extension;
        }

        return new Response($im->getCacheContent($format, $path), 200, ['Content-Type' => $contentType]);
    }
}
