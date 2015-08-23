<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class DeleteListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::RESPONSE => 'buildResponse',
        ];
    }

    /**
     * Set the appropriate status code when a resource is deleted
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function buildResponse(ResponseEvent $event)
    {
        if ($event->getAction() !== 'delete') {
            return;
        }

        $event
            ->getResponse()
            ->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
