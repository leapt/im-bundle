<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Controller;

use Leapt\ImBundle\Exception\RuntimeException;
use Leapt\ImBundle\Manager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController
{
    /**
     * Cached images are considered immutable for a given format & path, so browsers may keep them for a year.
     */
    private const int CACHE_MAX_AGE = 31536000;

    /**
     * Main action: renders the image cache (if not already done) and returns it to the browser.
     *
     * @param string $format A format name defined in config or a string [width]x[height]
     * @param string $path   The path of the source file (@see Manager::downloadExternalImage for more info on external/remote images)
     */
    public function index(Manager $im, Request $request, string $format, string $path): Response
    {
        // Prevent reading files outside the cache directory, as cached files are served without being regenerated
        if (\in_array('..', [$format, ...explode('/', $path)], true)) {
            throw new NotFoundHttpException(\sprintf('Invalid image path "%s" in format "%s"', $path, $format));
        }

        if (!$im->cacheExists($format, $path)) {
            if (str_starts_with($path, 'http/') || str_starts_with($path, 'https/')) {
                $newPath = $im->downloadExternalImage($format, $path);
                try {
                    $im->mogrify($format, $newPath);
                } catch (\Throwable $e) {
                    // Do not keep an invalid downloaded file, as it would then be served as a valid cached image
                    @unlink($newPath);

                    throw $e;
                }
            } else {
                $im->convert($format, $path);
            }

            if (!$im->cacheExists($format, $path)) {
                throw new RuntimeException(\sprintf('Caching of image failed for %s in %s format', $path, $format));
            }
        }

        $extension = pathinfo($path, \PATHINFO_EXTENSION);
        $contentType = $request->getMimeType($extension);
        if (empty($contentType)) {
            $contentType = 'image/' . $extension;
        }

        $response = new BinaryFileResponse($im->getCacheFilePath($format, $path), 200, ['Content-Type' => $contentType], true, null, true, true);
        $response->setMaxAge(self::CACHE_MAX_AGE);
        $response->isNotModified($request);

        return $response;
    }
}
