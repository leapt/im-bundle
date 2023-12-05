<?php

declare(strict_types=1);

namespace Leapt\ImBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class LeaptImBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $builder->setParameter('leapt_im.formats', $config['formats']);
        $builder->setParameter('leapt_im.public_path', $config['public_path']);
        $builder->setParameter('leapt_im.cache_path', $config['cache_path']);
        $builder->setParameter('leapt_im.timeout', $config['timeout']);
        $builder->setParameter('leapt_im.binary_path', $config['binary_path']);
    }
}
