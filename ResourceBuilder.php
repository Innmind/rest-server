<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ResourceBuilder
{
    protected $accessor;

    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * Build a resource object from a raw data
     * object following the given description
     *
     * @param object $data
     * @param ResourceDefinition $definition
     *
     * @throws InvalidArgumentException If the data is not an object
     * @throws NoSuchPropertyException If a property is not found in the data
     *
     * @return Resource
     */
    public function build($data, ResourceDefinition $definition)
    {
        if (!is_object($data)) {
            throw new \InvalidArgumentException(sprintf(
                'You must give a data object in order to build the resource %s',
                $definition
            ));
        }

        $resource = new Resource;
        $resource->setDefinition($definition);

        foreach ($definition->getProperties() as $property) {
            $value = $this->accessor->getValue($data, (string) $property);
            $resource->set($property, $value);
        }

        return $resource;
    }
}
