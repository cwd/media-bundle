<?php

namespace Cwd\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('cwd_media')->children()
            ->booleanNode('throw_exception')->defaultFalse()->end()
            ->scalarNode('entity_class')->defaultValue('Model:Media')->end()

            ->arrayNode('storage')->addDefaultsIfNotSet()->children()
                ->scalarNode('path')->defaultValue('%kernel.root_dir%/../mediastore')->end()
                ->scalarNode('depth')->defaultValue(4)->end()
            ->end()->end()

            ->arrayNode('cache')->addDefaultsIfNotSet()->children()
                ->scalarNode('dirname')->defaultValue('imagecache')->end()
                ->scalarNode('path')->defaultValue('%kernel.root_dir%/../web')->end()
            ->end()->end()

            ->arrayNode('converter')->addDefaultsIfNotSet()->children()
                ->scalarNode('quality')->defaultValue(90)->end()
                ->arrayNode('size')->addDefaultsIfNotSet()->children()
                    ->scalarNode('max_width')->defaultValue(2000)->end()
                    ->scalarNode('max_height')->defaultValue(2000)->end()
                ->end()->end()
            ->end()->end();

        return $treeBuilder;
    }
}
