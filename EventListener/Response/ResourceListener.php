<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\Resource;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ResourceListener implements EventSubscriberInterface
{
    protected $urlGenerator;
    protected $routeLoader;
    protected $serializer;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RouteLoader $routeLoader,
        SerializerInterface $serializer
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routeLoader = $routeLoader;
        $this->serializer = $serializer;
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

        if (!$content instanceof Resource) {
            return;
        }

        $definition = $content->getDefinition();
        $links = [];

        foreach ($definition->getProperties() as $property) {
            if (
                !$property->containsResource() ||
                $property->hasOption('inline')
            ) {
                continue;
            }

            if (!$content->has((string) $property)) {
                continue;
            }

            $subs = [];

            if ($property->getType() === 'resource') {
                $subs[] = $content->get((string) $property);
            } else {
                $subs = $content->get((string) $property);
            }

            foreach ($subs as $resource) {
                $def = $resource->getDefinition();
                $route = $this->routeLoader->getRoute(
                    $def,
                    'get'
                );
                $links[] = sprintf(
                    '<%s>; rel="property"; name="%s"',
                    $this->urlGenerator->generate(
                        $route,
                        ['id' => $resource->get($def->getId())]
                    ),
                    (string) $property
                );
            }
        }

        $response = $event->getResponse();
        $response->headers->add(['Link' => $links]);
        $response->setContent(
            $this->serializer->serialize(
                $content,
                $event->getRequest()->getRequestFormat(),
                ['definition' => $definition]
            )
        );
    }
}
