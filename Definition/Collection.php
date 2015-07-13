<?php

namespace Innmind\Rest\Server\Definition;

class Collection
{
    protected $name;
    protected $storage;
    protected $resources = [];

    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Return the collection name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the default storage to use for resources
     *
     * @param string $storage
     *
     * @return Collection self
     */
    public function setStorage($storage)
    {
        $this->storage = (string) $storage;

        return $this;
    }

    /**
     * Return the storage
     *
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Add a new resource definition to the collection
     *
     * @param Resource $resource
     *
     * @return Collection self
     */
    public function addResource(Resource $resource)
    {
        if (!$resource->hasStorage()) {
            if ($this->storage === null) {
                throw new \LogicException(sprintf(
                    'You must define a storage for "%s"',
                    $resource->getName()
                ));
            }

            $resource->setStorage($this->storage);
        }

        $this->resources[$resource->getName()] = $resource;
        $resource->setCollection($this);

        return $this;
    }

    /**
     * Return all the resources
     *
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Return a resource definition
     *
     * @param string $name
     *
     * @throws InvalidArgumentException If resource not found
     *
     * @return Resource
     */
    public function getResource($name)
    {
        if (!isset($this->resources[(string) $name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown resource "%s" in collection "%s"',
                $name,
                $this->name
            ));
        }

        return $this->resources[(string) $name];
    }

    /**
     * Check if the collection has the resource
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasResource($name)
    {
        return isset($this->resources[(string) $name]);
    }

    public function __toString()
    {
        return $this->name;
    }
}
