<?php

namespace Innmind\Rest\Server\CompilerPass;

use Innmind\Rest\Server\CompilerPassInterface;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Exception\ConfigurationException;

class ArrayTypePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(Registry $registry)
    {
        foreach ($registry->getCollections() as $collection) {
            foreach ($collection->getResources() as $resource) {
                foreach ($resource->getProperties() as $property) {
                    if (
                        $property->getType() === 'array' &&
                        !$property->hasOption('inner_type')
                    ) {
                        throw new ConfigurationException(sprintf(
                            'You must specify an "inner_type" for %s::%s.%s',
                            $collection,
                            $resource,
                            $property
                        ));
                    }
                }
            }
        }
    }
}
