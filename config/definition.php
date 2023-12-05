<?php

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->arrayNode('formats')
                ->useAttributeAsKey('key')
                ->prototype('variable')->end()
            ->end()
            ->scalarNode('cache_path')
                ->info('Relative path to the images cache folder (relative to web path).')
                ->defaultValue('cache/im')
            ->end()
            ->scalarNode('public_path')
                ->info('Relative path to the public folder (relative to project directory).')
                ->defaultValue('public')
            ->end()
            ->integerNode('timeout')
                ->info('Sets the process timeout (max. runtime).')
                ->defaultValue(60)
            ->end()
            ->scalarNode('binary_path')
                ->info('The path to Mogrify')
                ->example('/usr/bin/')
                ->defaultNull()
            ->end()
        ->end()
    ;
};
