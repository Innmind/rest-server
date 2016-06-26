<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Access;
use Symfony\Component\Config\Definition\{
    ConfigurationInterface,
    Builder\TreeBuilder
};

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('innmind_rest_server');

        $root
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->arrayNode('children')
                        ->info('Recursive directories of resources')
                        ->prototype('variable')->end()
                        ->defaultValue([])
                    ->end()
                    ->arrayNode('resources')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('identity')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('gateway')
                                    ->isRequired()
                                ->end()
                                ->arrayNode('properties')
                                    ->useAttributeAsKey('name')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('type')
                                                ->isRequired()
                                            ->end()
                                            ->arrayNode('access')
                                                ->prototype('scalar')->end()
                                                ->defaultValue([Access::READ])
                                            ->end()
                                            ->arrayNode('variants')
                                                ->prototype('scalar')->end()
                                                ->defaultValue([])
                                            ->end()
                                            ->booleanNode('optional')
                                                ->defaultFalse()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('options')
                                    ->prototype('variable')->end()
                                    ->defaultValue([])
                                ->end()
                                ->arrayNode('metas')
                                    ->prototype('variable')->end()
                                    ->defaultValue([])
                                ->end()
                                ->booleanNode('rangeable')
                                    ->defaultTrue()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
