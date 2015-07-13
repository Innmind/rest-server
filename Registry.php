<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Collection as CollectionDefinition;

class Registry
{
    protected $collections = [];

    /**
     * Add a collection of resources
     *
     * @param CollectionDefinition $collection
     *
     * @return Registry self
     */
    public function addCollection(CollectionDefinition $collection)
    {
        $this->collections[(string) $collection] = $collection;

        return $this;
    }

    /**
     * Return the collection
     *
     * @param string $name
     *
     * @throws LogicException If the collection is not found
     *
     * @return CollectionDefinition
     */
    public function getCollection($name)
    {
        if (!$this->hasCollection($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown collection "%s"',
                $name
            ));
        }

        return $this->collections[(string) $name];
    }

    /**
     * Check if the collection exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCollection($name)
    {
        return isset($this->collections[(string) $name]);
    }

    /**
     * Return all collections
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }
}
