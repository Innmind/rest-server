<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Routing\RouteFactory;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Formats;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

class ResourceListener implements EventSubscriberInterface
{
    protected $urlGenerator;
    protected $routeFactory;
    protected $serializer;
    protected $formats;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RouteFactory $routeFactory,
        SerializerInterface $serializer,
        Formats $formats
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routeFactory = $routeFactory;
        $this->serializer = $serializer;
        $this->formats = $formats;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'buildResponse',
        ];
    }

    /**
     * Build the response event for a resource
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @return void
     */
    public function buildResponse(GetResponseForControllerResultEvent $event)
    {
        $content = $event->getControllerResult();

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
                $route = $this->routeFactory->makeName(
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

        $format = $event->getRequest()->getRequestFormat();
        $response = new Response;
        $response->headers->add([
            'Link' => $links,
            'Content-Type' => $this->formats->getMediaType($format),
        ]);
        $response->setContent(
            $this->serializer->serialize(
                $content,
                $format,
                ['definition' => $definition]
            )
        );
        $event->setResponse($response);
    }
}
