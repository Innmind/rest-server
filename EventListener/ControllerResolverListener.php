<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\Controller\ResourceController;
use Innmind\Rest\Server\RouteKeys;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Inject the _controller attribute in the request so it can be
 * understood by the kernel controller resolver
 */
class ControllerResolverListener implements EventSubscriberInterface
{
    protected $controller;
    protected $actions = ['index', 'create', 'get', 'options', 'update', 'delete'];

    public function __construct(ResourceController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'injectController',
        ];
    }

    /**
     * Inject the controller attribute in the request
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function injectController(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::ACTION)) {
            throw new \LogicException('No action found');
        }

        $action = $request->attributes->get(RouteKeys::ACTION);

        if (!in_array($action, $this->actions, true)) {
            throw new \LogicException(sprintf(
                'Invalid action "%s"',
                $action
            ));
        }

        $request->attributes->set(
            '_controller',
            [$this->controller, sprintf('%sAction', $action)]
        );
    }
}
