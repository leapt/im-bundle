<?php

declare(strict_types=1);

use Leapt\ImBundle\Controller\DefaultController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('leapt_im_default_index', '%leapt_im.cache_path%/{format}/{path}')
        ->controller([DefaultController::class, 'index'])
        ->requirements(['path' => '(.+)'])
    ;
};
