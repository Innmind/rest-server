<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Resource;
use Symfony\Component\EventDispatcher\Event;

class PreCreateEvent extends Event
{
    protected $resource;
    protected $id;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Return the resource to be created
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Replace the resource to be created
     *
     * @param Innmind\Rest\Server\Resource $resource
     *
     * @throws InvalidArgumentException If no definition is set on the resource
     *
     * @return PreCreateEvent self
     */
    public function replaceResource(Resource $resource)
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
     * Check if a resource id has been set
     *
     * @return bool
     */
    public function hasResourceId()
    {
        return $this->id !== null;
    }

    /**
     * Return the resource id
     *
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->id;
    }

    /**
     * Set the resource id, meaning you already have created the entity
     *
     * @param mixed $id
     *
     * @return PreCreateEvent self
     */
    public function setResourceId($id)
    {
        $this->id = $id;

        return $this;
    }
}
