<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Resource as Definition;

class Resource
{
    protected $data = [];
    protected $definition;

    /**
     * Add the given property to the resource
     *
     * @param string $property
     * @param mixed $value
     *
     * @return Resource self
     */
    public function set($property, $value)
    {
        $this->data[(string) $property] = $value;

        return $this;
    }

    /**
     * Return the given property value
     *
     * @param string $property
     *
     * @throws InvalidArgumentException If the property is not found
     *
     * @return mixed
     */
    public function get($property)
    {
        if (!$this->has($property)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown property "%s"',
                $property
            ));
        }

        return $this->data[(string) $property];
    }

    /**
     * Check if the resource has the given property
     *
     * @param string $property
     *
     * @return bool
     */
    public function has($property)
    {
        return isset($this->data[(string) $property]);
    }

    /**
     * Return all the properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->data;
    }

    /**
     * Set the resource definition
     *
     * @param Definition $definition
     *
     * @return Resource self
     */
    public function setDefinition(Definition $definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Return the resource definition
     *
     * @return Definition
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
