<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\ResourceDefinition;

interface StorageInterface
{
    /**
     * Return all the entities matching the given resource definition
     * or the one with the given id
     *
     * @param ResourceDefinition $definition
     * @param mixed $id
     *
     * @return Collection
     */
    public function read(ResourceDefinition $definition, $id = null);

    /**
     * Create the given resource
     *
     * @param Innmind\Rest\Server\Resource $resource
     *
     * @return mixed The id for the given resource
     */
    public function create(Resource $resource);

    /**
     * Update the given resource
     *
     * @param Innmind\Rest\Server\Resource $resource
     * @param mixed $id
     *
     * @return StorageInterface self
     */
    public function update(Resource $resource, $id);

    /**
     * Delete the resource with the given id
     *
     * @param ResourceDefinition $definition
     * @param mixed $id
     *
     * @return StorageInterface self
     */
    public function delete(ResourceDefinition $definition, $id);

    /**
     * Check if the resource definition is correctly configured for the storage
     *
     * @param ResourceDefinition $definition
     *
     * @return bool
     */
    public function supports(ResourceDefinition $definition);
}
