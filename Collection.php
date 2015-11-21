<?php

namespace Innmind\Rest\Server;

class Collection implements \ArrayAccess, \Iterator, \Countable
{
    protected $resources = [];

    /**
     * Check if the resource is in this collection
     *
     * @param HttpResourceInterface $resource
     *
     * @return bool
     */
    public function contains(HttpResourceInterface $resource)
    {
        return in_array($resource, $this->resources, true);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($idx, $resource)
    {
        if ($idx === null) {
            $this->resources[] = $resource;
        } else {
            $this->resources[$idx] = $resource;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->resources[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->resources[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->resources[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->key() !== null;
    }
}
