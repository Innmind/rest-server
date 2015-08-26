<?php

namespace Innmind\Rest\Server;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('innmind_rest_server');

        $root
            ->children()
                ->append($this->getCollectionNode())
            ->end();

        return $builder;
    }

    /**
     * Return the node to specify the resources collections
     *
     * @return NodeInterface
     */
    public function getCollectionNode()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('collections');

        $root
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('storage')
                        ->isRequired()
                    ->end()
                    ->arrayNode('resources')
                        ->useAttributeAsKey('name')
                        ->requiresAtLeastOneElement()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('id')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('storage')
                                    ->isRequired()
                                ->end()
                                ->arrayNode('properties')
                                    ->useAttributeAsKey('name')
                                    ->requiresAtLeastOneElement()
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('type')
                                                ->isRequired()
                                            ->end()
                                            ->arrayNode('access')
                                                ->isRequired()
                                                ->prototype('scalar')->end()
                                            ->end()
                                            ->arrayNode('variants')
                                                ->defaultValue([])
                                                ->prototype('scalar')->end()
                                            ->end()
                                            ->arrayNode('options')
                                                ->prototype('variable')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('meta')
                                    ->prototype('variable')->end()
                                ->end()
                                ->arrayNode('options')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $root;
    }
}
