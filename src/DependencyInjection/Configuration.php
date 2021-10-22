<?php

declare(strict_types=1);

namespace Leapt\ImBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('leapt_im');
        $rootNode = $treeBuilder->getRootNode();
        \assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
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
            ->end();

        return $treeBuilder;
    }
}
