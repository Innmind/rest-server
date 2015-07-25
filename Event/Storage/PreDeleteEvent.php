<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Definition\Resource;
use Symfony\Component\EventDispatcher\Event;

class PreDeleteEvent extends Event
{
    protected $definition;
    protected $id;

    public function __construct(Resource $definition, $id)
    {
        $this->definition = $definition;
        $this->id = $id;
    }

    /**
     * Return the resource definition
     *
     * @return Resource
     */
    public function getDefinition()
    {
        return $this->definition;
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
