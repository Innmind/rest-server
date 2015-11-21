<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\ResourceDefinition;

/**
 * Describe a resource that can be received by the api or be sent back to a user
 */
interface HttpResourceInterface
{
    /**
     * Add the given property to the resource
     *
     * @param string $property
     * @param mixed $value
     *
     * @return HttpResourceInterface self
     */
    public function set($property, $value);

    /**
     * Return the given property value
     *
     * @param string $property
     *
     * @throws InvalidArgumentException If the property is not found
     *
     * @return mixed
     */
    public function get($property);

    /**
     * Check if the resource has the given property
     *
     * @param string $property
     *
     * @return bool
     */
    public function has($property);

    /**
     * Return all the properties
     *
     * @return array
     */
    public function getProperties();

    /**
     * Set the resource definition
     *
     * @param ResourceDefinition $definition
     *
     * @return HttpResourceInterface self
     */
    public function setDefinition(ResourceDefinition $definition);

    /**
     * Return the resource definition
     *
     * @return ResourceDefinition
     */
    public function getDefinition();

    /**
     * Check if the resource has a definition attached
     *
     * @return bool
     */
    public function hasDefinition();
}
