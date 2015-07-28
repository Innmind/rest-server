<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Resource;
use Symfony\Component\EventDispatcher\Event;

class PostUpdateEvent extends Event
{
    protected $resource;
    protected $id;
    protected $entity;

    public function __construct(Resource $resource, $id, $entity)
    {
        $this->resource = $resource;
        $this->id = $id;
        $this->entity = $entity;
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
     * Return the id of the resource
     *
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->id;
    }

    /**
     * Return the entity that has been updated
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
