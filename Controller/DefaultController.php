<?php

namespace Leapt\ImBundle\Controller;

use Leapt\ImBundle\Exception\RuntimeException;
use Leapt\ImBundle\Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controls calls to resized images
 */
class DefaultController extends AbstractController
{
    /**
     * Main action: renders the image cache and returns it to the browser
     *
     * @param Manager $im
     * @param Request $request
     * @param string $format A format name defined in config or a string [width]x[height]
     * @param string $path The path of the source file (@see Manager::downloadExternalImage for more info on external/remote images)
     *
     * @return Response
     */
    public function indexAction(Manager $im, Request $request, $format, $path)
    {
        if (strpos($path, "http/") === 0 || strpos($path, "https/") === 0) {
            $newPath = $im->downloadExternalImage($format, $path);
            $im->mogrify($format, $newPath);
        } else {
            $im->convert($format, $path);
        }

        if (!$im->cacheExists($format, $path)) {
            throw new RuntimeException(sprintf("Caching of image failed for %s in %s format", $path, $format));
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $contentType = $request->getMimeType($extension);
            if (empty($contentType)) {
                $contentType = 'image/' . $extension;
            }

            return new Response($im->getCacheContent($format, $path), 200, ['Content-Type' => $contentType]);
        }
    }
}
