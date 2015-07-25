<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Resource;
use Symfony\Component\EventDispatcher\Event;

class PostUpdateEvent extends Event
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
     * Return the id of the resource
     *
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->id;
    }
}
