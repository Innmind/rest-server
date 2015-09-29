<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Routing\RouteFinder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

class CreateListener implements EventSubscriberInterface
{
    protected $urlGenerator;
    protected $routeFinder;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RouteFinder $routeFinder
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routeFinder = $routeFinder;
    }

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
     * Determine the response code to set for this response
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function buildResponse(ResponseEvent $event)
    {
        if ($event->getAction() !== 'create') {
            return;
        }

        if ($event->getContent() instanceof Collection) {
            $event
                ->getResponse()
                ->setStatusCode(Response::HTTP_MULTIPLE_CHOICES);

            return;
        }

        $event
            ->getResponse()
            ->setStatusCode(Response::HTTP_CREATED);

        if (!$event->getContent() instanceof Resource) {
            return;
        }

        $resource = $event->getContent();
        $definition = $resource->getDefinition();
        $route = $this->routeFinder->find(
            $definition,
            'get'
        );
        $event
            ->getResponse()
            ->headers
            ->add([
                'Location' => $this->urlGenerator->generate(
                    $route,
                    ['id' => $resource->get($definition->getId())]
                )
            ]);
    }
}
