<?php

declare(strict_types=1);

use Leapt\ImBundle\Form\Extension\ImageTypeExtension;
use Leapt\ImBundle\Listener\MogrifySubscriber;
use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Wrapper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Process\Process;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
            ->autowire(true)
            ->autoconfigure(true)
            ->private()

        ->load('Leapt\\ImBundle\\', __DIR__ . '/../../*')

        ->set(Wrapper::class)
            ->arg('$processClass', Process::class)
            ->arg('$binaryPath', param('leapt_im.binary_path'))
            ->arg('$timeout', param('leapt_im.timeout'))

        ->set(Manager::class)
            ->arg('$wrapper', service(Wrapper::class))
            ->arg('$projectDir', param('kernel.project_dir'))
            ->arg('$publicPath', param('leapt_im.public_path'))
            ->arg('$cachePath', param('leapt_im.cache_path'))
            ->arg('$formats', param('leapt_im.formats'))

        ->set(MogrifySubscriber::class)
            ->tag('doctrine.event_subscriber')

        ->load('Leapt\\ImBundle\\Controller\\', __DIR__ . '/../../Controller')
            ->tag('controller.service_arguments')

        ->set(ImageTypeExtension::class)
            ->tag('form.type_extension')
    ;
};
