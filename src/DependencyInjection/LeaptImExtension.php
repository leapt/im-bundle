<?php

declare(strict_types=1);

namespace Leapt\ImBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @codeCoverageIgnore
 */
class LeaptImExtension extends Extension
{
    /**
     * @param array<string|array<mixed|array<mixed>>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('leapt_im.formats', $config['formats']);
        $container->setParameter('leapt_im.public_path', $config['public_path']);
        $container->setParameter('leapt_im.cache_path', $config['cache_path']);
        $container->setParameter('leapt_im.timeout', $config['timeout']);
        $container->setParameter('leapt_im.binary_path', $config['binary_path']);
    }
}
