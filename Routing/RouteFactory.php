<?php

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteFactory
{
    const NAME_PATTERN = 'innmind_rest_%s_%s_%s';

    /**
     * Build the route for the given action
     *
     * @param ResourceDefinition $resource
     * @param string $action
     *
     * @return Route
     */
    public function makeRoute(ResourceDefinition $resource, $action)
    {
        switch ($action) {
            case RouteActions::INDEX:
                $path = '/%s/%s/';
                $method = 'GET';
                break;
            case RouteActions::GET:
                $path = '/%s/%s/{id}';
                $method = 'GET';
                break;
            case RouteActions::CREATE:
                $path = '/%s/%s/';
                $method = 'POST';
                break;
            case RouteActions::OPTIONS:
                $path = '/%s/%s/';
                $method = 'OPTIONS';
                break;
            case RouteActions::UPDATE:
                $path = '/%s/%s/{id}';
                $method = 'PUT';
                break;
            case RouteActions::DELETE:
                $path = '/%s/%s/{id}';
                $method = 'DELETE';
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'The route action "%s" is not recognized',
                    $action
                ));
        }

        $route = new Route(
            sprintf(
                $path,
                $resource->getCollection(),
                $resource
            ),
            [
                RouteKeys::DEFINITION => sprintf(
                    '%s::%s',
                    $resource->getCollection(),
                    $resource
                ),
                RouteKeys::ACTION => $action,
            ]);
        $route->setMethods($method);

        return $route;
    }

    /**
     * Build all the routes for the given resource
     *
     * @param ResourceDefinition $resource
     *
     * @return RouteCollection
     */
    public function makeRoutes(ResourceDefinition $resource)
    {
        $collection = new RouteCollection;

        foreach (RouteActions::all() as $action) {
            $collection->add(
                $this->makeName($resource, $action),
                $this->makeRoute($resource, $action)
            );
        }

        return $collection;
    }

    /**
     * Make the route name for the given route and action
     *
     * @param ResourceDefinition $resource
     * @param string $action
     *
     * @return string
     */
    public function makeName(ResourceDefinition $resource, $action)
    {
        return sprintf(
            self::NAME_PATTERN,
            $resource->getCollection(),
            $resource,
            $action
        );
    }
}
