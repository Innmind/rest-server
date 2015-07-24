<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\RouteEvent;
use Innmind\Rest\Server\Routing\RouteCollection;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection as SFRouteCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RouteLoader extends Loader
{
    CONST RESOURCE_KEY = '_rest_resource';
    CONST ACTION_KEY = '_rest_action';

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

        $routes = new SFRouteCollection;
        $collections = $this->registry->getCollections();

        foreach ($collections as $collection) {
            $resources = $collection->getResources();

            foreach ($resources as $resource) {
                $resourceRoutes = new RouteCollection($resource);
                $resourceRoutes->addPrefix($this->prefix);

                foreach ($resourceRoutes as $name => $route) {
                    $this->dispatcher->dispatch(
                        Events::ROUTE,
                        new RouteEvent($routes, $route, $resource)
                    );
                }

                $routes->addCollection($resourceRoutes);
            }
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'innmind_rest';
    }
}
