<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\RouteKeys;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class DeleteListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [['buildResponse', 10]],
        ];
    }

    /**
     * Set the appropriate status code when a resource is deleted
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @return void
     */
    public function buildResponse(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (
            !$request->attributes->has(RouteKeys::ACTION) ||
            $request->attributes->get(RouteKeys::ACTION) !== 'delete'
        ) {
            return;
        }

        $event->setResponse(new Response('', Response::HTTP_NO_CONTENT));
    }
}
