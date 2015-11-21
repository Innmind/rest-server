<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\HttpResourceInterface;
use Symfony\Component\EventDispatcher\Event;

class PreUpdateEvent extends Event
{
    protected $resource;
    protected $id;

    public function __construct(HttpResourceInterface $resource, $id)
    {
        $this->resource = $resource;
        $this->id = $id;
    }

    /**
     * Return the resource to be updated
     *
     * @return HttpResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Replace the resource with the given one
     *
     * @param HttpResourceInterface $resource
     *
     * @throws InvalidArgumentException If the resource has no definition
     *
     * @return PreUpdateEvent self
     */
    public function replaceResource(HttpResourceInterface $resource)
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
