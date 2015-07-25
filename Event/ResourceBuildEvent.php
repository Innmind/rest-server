<?php

namespace Innmind\Rest\Server\Event;

use Innmind\Rest\Server\Definition\Resource;
use Symfony\Component\EventDispatcher\Event;

class ResourceBuildEvent extends Event
{
    protected $data;
    protected $definition;

    public function __construct($data, Resource $definition)
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
     * @return Resource
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
