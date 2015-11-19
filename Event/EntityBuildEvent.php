<?php

namespace Innmind\Rest\Server\Event;

use Innmind\Rest\Server\HttpResourceInterface;
use Symfony\Component\EventDispatcher\Event;

class EntityBuildEvent extends Event
{
    protected $resource;
    protected $entity;

    public function __construct(HttpResourceInterface $resource, $entity)
    {
        $this->resource = $resource;
        $this->entity = $entity;
    }

    /**
     * Return the resource which contains the new data
     *
     * @return HttpResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Return the entity on which the data will be set
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
