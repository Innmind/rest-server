<?php

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Definition\Resource;
use Symfony\Component\Routing\RouteCollection as SFRouteCollection;
use Symfony\Component\Routing\Route;

class RouteCollection extends SFRouteCollection
{
    CONST RESOURCE_KEY = '_rest_resource';
    CONST ACTION_KEY = '_rest_action';

    /**
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this
            ->buildIndex($resource)
            ->buildCreate($resource)
            ->buildGet($resource)
            ->buildUpdate($resource)
            ->buildDelete($resource)
            ->buildOptions($resource);
    }

    /**
     * Build the route to list resources
     *
     * @param Resource $resource
     *
     * @return RouteCollection self
     */
    protected function buildIndex(Resource $resource)
    {
        $path = sprintf(
            '/%s/%s/',
            $resource->getCollection(),
            $resource
        );

        $route = new Route($path, [
            self::RESOURCE_KEY => $resource,
            self::ACTION_KEY => 'index',
        ]);
        $route->setMethods('GET');

        $this->addResourceRoute($route);

        return $this;
    }

    /**
     * Build route to create a resource
     *
     * @param Resource $resource
     *
     * @return RouteCollection self
     */
    protected function buildCreate(Resource $resource)
    {
        $route = clone $this->get($this->buildRouteName($resource, 'index'));
        $route
            ->setDefault(self::ACTION_KEY, 'create')
            ->setMethods('POST');
        $this->addResourceRoute($route);

        return $this;
    }

    /**
     * Build route to expose resource configuration
     *
     * @param Resource $resource
     *
     * @return RouteCollection self
     */
    protected function buildOptions(Resource $resource)
    {
        $route = clone $this->get($this->buildRouteName($resource, 'index'));
        $route
            ->setDefault(self::ACTION_KEY, 'options')
            ->setMethods('OPTIONS');
        $this->addResourceRoute($route);

        return $this;
    }

    /**
     * Build route to get a resource
     *
     * @param Resource $resource
     *
     * @return RouteCollection self
     */
    protected function buildGet(Resource $resource)
    {
        $path = sprintf(
            '/%s/%s/{id}',
            $resource->getCollection(),
            $resource
        );

        $route = new Route($path, [
            self::RESOURCE_KEY => $resource,
            self::ACTION_KEY => 'get',
        ]);
        $route->setMethods('GET');

        $this->addResourceRoute($route);

        return $this;
    }

    /**
     * Build route to update a resource
     *
     * @param Resource $resource
     *
     * @return RouteCollection self
     */
    protected function buildUpdate(Resource $resource)
    {
        $route = clone $this->get($this->buildRouteName($resource, 'get'));
        $route
            ->setDefault(self::ACTION_KEY, 'update')
            ->setMethods('PUT');
        $this->addResourceRoute($route);

        return $this;
    }

    /**
     * Build route to remove a resource
     *
     * @param Resource $resource
     *
     * @return RouteCollection self
     */
    protected function buildDelete(Resource $resource)
    {
        $route = clone $this->get($this->buildRouteName($resource, 'get'));
        $route
            ->setDefault(self::ACTION_KEY, 'delete')
            ->setMethods('DELETE');
        $this->addResourceRoute($route);

        return $this;
    }

    /**
     * Add the given route to the collection
     *
     * @param Route $route
     *
     * @return void
     */
    protected function addResourceRoute(Route $route)
    {
        $this->add(
            $this->buildRouteName(
                $route->getDefault(self::RESOURCE_KEY),
                $route->getDefault(self::ACTION_KEY)
            ),
            $route
        );
    }

    /**
     * Build the route name
     *
     * @param Resource $resource
     * @param string $action
     *
     * @return string
     */
    protected function buildRouteName(Resource $resource, $action)
    {
        return sprintf(
            'innmind_rest_%s_%s_%s',
            $resource->getCollection(),
            $resource,
            $action
        );
    }
}
