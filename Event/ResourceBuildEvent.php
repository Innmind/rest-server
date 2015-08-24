<?php

namespace Innmind\Rest\Server\Event;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\EventDispatcher\Event;

class ResourceBuildEvent extends Event
{
    protected $data;
    protected $definition;
    protected $resource;

    public function __construct($data, Definition $definition)
    {
        $this->data = $data;
        $this->definition = $definition;
    }

    /**
     * Return the data to be used to build a resource
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Replace the current data with the given one
     *
     * @param object $data
     *
     * @return ResourceBuildEvent self
     */
    public function replaceData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Return the resource definition
     *
     * @return Definition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set your own built resource, prevent default behavior
     *
     * @param Innmind\Rest\Server\Resource $resource
     *
     * @throws InvalidArgumentException If no definition attached to the resource
     *
     * @return ResourceBuildEvent self
     */
    public function setResource(Resource $resource)
    {
        if (!$resource->hasDefinition()) {
            throw new \InvalidArgumentException(
                'A resource must have a definition'
            );
        }

        $this->resource = $resource;

        return $this;
    }

    /**
     * Check if a resource has been set
     *
     * @return bool
     */
    public function hasResource()
    {
        return $this->resource !== null;
    }

    /**
     * Return the resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}