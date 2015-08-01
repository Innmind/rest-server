<?php

namespace Innmind\Rest\Server\CompilerPass;

use Innmind\Rest\Server\CompilerPassInterface;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Definition\Type\ResourceType;
use Innmind\Rest\Server\Definition\Type\ArrayType;
use Innmind\Rest\Server\Exception\ConfigurationException;

class SubResourcePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(Registry $registry)
    {
        $resourceType = (string) new ResourceType;
        $arrayType = (string) new ArrayType;

        foreach ($registry->getCollections() as $collection) {
            foreach ($collection->getResources() as $resource) {
                foreach ($resource->getProperties() as $property) {
                    if (
                        $property->getType() === $resourceType ||
                        (
                            $property->getType() === $arrayType &&
                            $property->getOption('inner_type') === $resourceType
                        )
                    ) {
                        if (!$property->hasOption('resource')) {
                            throw new ConfigurationException(sprintf(
                                'You must specify the resource name for %s::%s.%s',
                                $collection,
                                $resource,
                                $property
                            ));
                        }

                        $subResource = $property->getOption('resource');
                        $subCollection = $collection;

                        if ($property->hasOption('collection')) {
                            $subCollection = $property->getOption('collection');
                            $property->addOption('collection', null);
                        }

                        if (!$registry->hasCollection($subCollection)) {
                            throw new ConfigurationException(sprintf(
                                'Unknown collection "%s" for sub resource on %s::%s.%s',
                                $subCollection,
                                $collection,
                                $resource,
                                $property
                            ));
                        }

                        $subCollection = $registry->getCollection($subCollection);

                        if (!$subCollection->hasResource($subResource)) {
                            throw new ConfigurationException(sprintf(
                                'Unknown resource "%s" in "%s" collection',
                                $subResource,
                                $subCollection
                            ));
                        }

                        $property->addOption(
                            'resource',
                            $subCollection->getResource($subResource)
                        );
                    }
                }
            }
        }
    }
}
