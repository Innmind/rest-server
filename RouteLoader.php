<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\RouteEvent;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RouteLoader extends Loader
{
    CONST RESOURCE_KEY = '_rest_resource';

    protected $dispatcher;
    protected $registry;
    protected $prefix;
    protected $loaded = false;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        Registry $registry,
        $prefix = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->registry = $registry;
        $this->prefix = '/' . rtrim(ltrim((string) $prefix, '/'), '/');
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if ($this->loaded) {
            throw new \LogicException(
                'Do not add the "innmind_rest" loader twice'
            );
        }

        $routes = new RouteCollection;
        $collections = $this->registry->getCollections();

        foreach ($collections as $collection) {
            $resources = $collection->getResources();

            foreach ($resources as $resource) {
                foreach ($this->buildRoutes($resource) as $name => $route) {
                    $this->dispatcher->dispatch(
                        Events::ROUTE,
                        new RouteEvent($routes, $route, $resource)
                    );
                    $routes->add($name, $route);
                }
            }
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'innmind_rest';
    }

    /**
     * Build all routes for the given resource
     *
     * @param ResourceDefinition $resource
     *
     * @return array
     */
    protected function buildRoutes(ResourceDefinition $resource)
    {
        $listName = sprintf(
            'innmind_rest_%s_%s_list',
            $resource->getCollection(),
            $resource
        );
        $createName = sprintf(
            'innmind_rest_%s_%s_create',
            $resource->getCollection(),
            $resource
        );
        $getName = sprintf(
            'innmind_rest_%s_%s_get',
            $resource->getCollection(),
            $resource
        );
        $updateName = sprintf(
            'innmind_rest_%s_%s_update',
            $resource->getCollection(),
            $resource
        );
        $deleteName = sprintf(
            'innmind_rest_%s_%s_delete',
            $resource->getCollection(),
            $resource
        );
        $optionsName = sprintf(
            'innmind_rest_%s_%s_options',
            $resource->getCollection(),
            $resource
        );

        return [
            $listName => $this->createListRoute($resource),
            $createName => $this->createResourceCreationRoute($resource),
            $getName => $this->createGetRoute($resource),
            $updateName => $this->createUpdateRoute($resource),
            $deleteName => $this->createDeleteRoute($resource),
            $optionsName => $this->createOptionsRoute($resource),
        ];
    }

    /**
     * Create a route to list all resources
     *
     * @param ResourceDefinition $resource
     *
     * @return Route
     */
    protected function createListRoute(ResourceDefinition $resource)
    {
        $path = sprintf(
            '%s/%s/%s/',
            $this->prefix,
            $resource->getCollection(),
            $resource
        );

        $route = new Route($path, [self::RESOURCE_KEY => $resource]);
        $route->setMethods('GET');

        return $route;
    }

    /**
     * Create a route to create a resource
     *
     * @param ResourceDefinition $resource
     *
     * @return Route
     */
    protected function createResourceCreationRoute(ResourceDefinition $resource)
    {
        return $this
            ->createListRoute($resource)
            ->setMethods('POST');
    }

    /**
     * Create a route to retrieve a resource by id
     *
     * @param ResourceDefinition $resource
     *
     * @return Route
     */
    protected function createGetRoute(ResourceDefinition $resource)
    {
        $path = sprintf(
            '%s/%s/%s/{id}',
            $this->prefix,
            $resource->getCollection(),
            $resource
        );

        $route = new Route($path, [self::RESOURCE_KEY => $resource]);
        $route->setMethods('GET');

        return $route;
    }

    /**
     * Create a route to update a resource
     *
     * @param ResourceDefinition $resource
     *
     * @return Route
     */
    protected function createUpdateRoute(ResourceDefinition $resource)
    {
        return $this
            ->createGetRoute($resource)
            ->setMethods('PUT');
    }

    /**
     * Create a route to delete a resource
     *
     * @param ResourceDefinition $resource
     *
     * @return Route
     */
    protected function createDeleteRoute(ResourceDefinition $resource)
    {
        return $this
            ->createGetRoute($resource)
            ->setMethods('DELETE');
    }

    /**
     * Create a route to expose the resource definition
     *
     * @param ResourceDefinition $resource
     *
     * @return Route
     */
    protected function createOptionsRoute(ResourceDefinition $resource)
    {
        return $this
            ->createListRoute($resource)
            ->setMethods('OPTIONS');
    }
}
