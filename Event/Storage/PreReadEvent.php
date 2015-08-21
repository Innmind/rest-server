<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Symfony\Component\EventDispatcher\Event;

class PreReadEvent extends Event
{
    protected $definition;
    protected $id;
    protected $resources;

    public function __construct(ResourceDefinition $definition, $id)
    {
        $this->definition = $definition;
        $this->id = $id;
        $this->resources = new Collection;
    }

    /**
     * Return the definition to use to read resources
     *
     * @return ResourceDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Check if a specific id is requested
     *
     * @return bool
     */
    public function hasId()
    {
        return $this->id !== null;
    }

    /**
     * Return the specific id requested
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add a resource to return to the user
     *
     * @param Resource $resource
     *
     * @return PrereadEvent self
     */
    public function addResource(Resource $resource)
    {
        $this->validateResource($resource);
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Use the given set of resources to be returned to the client
     *
     * @param Collection $resources
     *
     * @return PreReadEvent self
     */
    public function useResources(Collection $resources)
    {
        foreach ($resources as $resource) {
            $this->validateResource($resource);
        }

        $this->resources = $resources;

        return $this;
    }

    /**
     * Check if resources has been defined to be returned to the user
     *
     * @return bool
     */
    public function hasResources()
    {
        return $this->resources->count() > 0;
    }

    /**
     * Return the resources bag
     *
     * @return Collection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Check if a resource is valid
     *
     * @param Resource $resource
     *
     * @throws InvalidArgumentException If the resource has no definition attached
     *
     * @return void
     */
    protected function validateResource(Resource $resource)
    {
        if (!$resource->hasDefinition()) {
            throw new \InvalidArgumentException(
                'A resource must have a definition'
            );
        }
    }
}
