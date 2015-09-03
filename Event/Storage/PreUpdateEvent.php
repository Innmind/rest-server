<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Resource;
use Symfony\Component\EventDispatcher\Event;

class PreUpdateEvent extends Event
{
    protected $resource;
    protected $id;

    public function __construct(Resource $resource, $id)
    {
        $this->resource = $resource;
        $this->id = $id;
    }

    /**
     * Return the resource to be updated
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Replace the resource with the given one
     *
     * @param Innmind\Rest\Server\Resource $resource
     *
     * @throws InvalidArgumentException If the resource has no definition
     *
     * @return PreUpdateEvent self
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
     * Return the id of the resource
     *
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->id;
    }
}
