<?php

namespace Innmind\Rest\Server\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RouterListener implements EventSubscriberInterface
{
    protected $router;
    protected $requestStack;

    public function __construct(Router $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['matchRoute', 32]],
            KernelEvents::FINISH_REQUEST => 'updateStack',
        ];
    }

    /**
     * Load the routes and check which one is requested
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function matchRoute(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $this->requestStack->push($request);
        $parameters = $this->router->matchRequest($request);
        $request->attributes->add($parameters);
    }

    /**
     * Remove the request from the request stack
     *
     * @return void
     */
    public function updateStack()
    {
        $this->requestStack->pop();
    }
}
