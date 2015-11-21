<?php

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Event\RouteEvent;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RouteLoader extends Loader
{
    protected $dispatcher;
    protected $registry;
    protected $factory;
    protected $routes;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        Registry $registry,
        RouteFactory $factory
    ) {
        $this->dispatcher = $dispatcher;
        $this->registry = $registry;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if ($this->routes !== null) {
            throw new \LogicException(
                'Do not add the "innmind_rest" loader twice'
            );
        }

        $routes = new RouteCollection;
        $collections = $this->registry->getCollections();

        foreach ($collections as $collection) {
            $resources = $collection->getResources();

            foreach ($resources as $resource) {
                $resourceRoutes = $this->factory->makeRoutes($resource);

                foreach ($resourceRoutes as $name => $route) {
                    $event = $this->dispatcher->dispatch(
                        Events::ROUTE,
                        new RouteEvent($routes, $route, $resource)
                    );

                    if (!$event->isPropagationStopped()) {
                        $routes->add($name, $route);
                    }
                }
            }
        }

        $this->routes = $routes;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'innmind_rest';
    }
}
