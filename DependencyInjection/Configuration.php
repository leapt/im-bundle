<?php

namespace Leapt\ImBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @codeCoverageIgnore
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('leapt_im');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('leapt_im');
        }

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
                ->scalarNode('web_path')
                    ->info('Relative path to the public folder (relative to root directory).')
                    ->defaultValue('../public')
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
