<?php

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\RouteKeys;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;

class ResourceController
{
    protected $storages;

    public function __construct(Storages $storages)
    {
        $this->storages = $storages;
    }

    /**
     * Expose the list of resources for the given resource definition
     *
     * @param ResourceDefinition $definition
     *
     * @return Collection
     */
    public function indexAction(ResourceDefinition $definition)
    {
        return $this
            ->storages
            ->get($definition->getStorage())
            ->read($definition);
    }

    /**
     * Return a single resource
     *
     * @param ResourceDefinition $definition
     * @param string $id
     *
     * @return Resource
     */
    public function getAction(ResourceDefinition $definition, $id)
    {
        $storage = $this
            ->storages
            ->get($definition->getStorage());
        $resources = $storage->read($definition, $id);

        if ($resources->count() < 1) {
            throw new ResourceNotFoundException;
        } else if ($resources->count() > 1) {
            throw new TooManyResourcesFoundException;
        }

        return $resources->current();
    }

    /**
     * Create a resource
     *
     * @param Innmind\Rest\Resource|Collection $resources
     *
     * @return Innmind\Rest\Resource|Collection
     */
    public function createAction($resources)
    {
        if ($resources instanceof Collection) {
            foreach ($resources as $resource) {
                $this->createAction($resource);
            }
        } else {
            $storage = $this
                ->storages
                ->get($resources->getDefinition()->getStorage());
            $id = $storage->create($resources);
            $resources->set(
                $resources->getDefinition()->getId(),
                $id
            );
        }

        return $resources;
    }

    /**
     * Update a resource
     *
     * @param Innmind\Rest\Server\Resource $resource
     * @param string $id
     *
     * @return Innmind\Rest\Server\Resource
     */
    public function updateAction(Resource $resource, $id)
    {
        $this
            ->storages
            ->get($resource->getDefinition()->getStorage())
            ->update($resource, $id);

        return $this->getAction($resource->getDefinition(), $id);
    }

    /**
     * Delete a resource
     *
     * @param ResourceDefinition $definition
     * @param string $id
     *
     * @return void
     */
    public function deleteAction(ResourceDefinition $definition, $id)
    {
        $this
            ->storages
            ->get($definition->getStorage())
            ->delete($definition, $id);
    }

    /**
     * Format the resource description for the outside world
     * without exposing sensitive data
     *
     * @param ResourceDefinition $definition
     *
     * @return array
     */
    public function optionsAction(ResourceDefinition $definition)
    {
        $output = [
            'id' => $definition->getId(),
            'properties' => [],
        ];

        foreach ($definition->getproperties() as $property) {
            $output['properties'][(string) $property] = [
                'type' => $property->getType(),
                'access' => $property->getAccess(),
                'variants' => $property->getVariants(),
            ];

            if ($property->getType() === 'array') {
                $output['properties'][(string) $property]['inner_type'] = $property->getOption('inner_type');
            }

            if ($property->hasOption('optional')) {
                $output['properties'][(string) $property]['optional'] = true;
            }

            if ($property->containsResource()) {
                $sub = $property->getOption('resource');
                $output['properties'][(string) $property]['resource'] = $sub;
            }
        }

        if ($metas = $definition->getMetas()) {
            $output['meta'] = $metas;
        }

        return ['resource' => $output];
    }
}
