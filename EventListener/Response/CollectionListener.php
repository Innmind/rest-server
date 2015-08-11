<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\RouteLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CollectionListener implements EventSubscriberInterface
{
    protected $urlGenerator;
    protected $routeLoader;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RouteLoader $routeLoader
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routeLoader = $routeLoader;
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
     * Build the response event for a list of resources
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function buildResponse(ResponseEvent $event)
    {
        $content = $event->getContent();

        if (!$content instanceof \SplObjectStorage) {
            return;
        }

        $links = [];

        foreach ($content as $resource) {
            $definition = $resource->getDefinition();
            $route = $this->routeLoader->getRoute(
                $definition,
                'get'
            );
            $links[] = sprintf(
                '<%s>; rel="resource"',
                $this->urlGenerator->generate(
                    $route,
                    ['id' => $resource->get($definition->getId())]
                )
            );
        }

        $event
            ->getResponse()
            ->headers
            ->add(['Link' => $links]);
    }
}
