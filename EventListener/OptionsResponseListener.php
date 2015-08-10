<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\RouteLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

class OptionsResponseListener implements EventSubscriberInterface
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
     * Build the response for an OPTIONS request
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function buildResponse(ResponseEvent $event)
    {
        if ($event->getAction() !== 'options') {
            return;
        }

        $content = $event->getContent();
        $response = $event->getResponse();

        foreach ($content['resource']['properties'] as $name => $property) {
            if (!isset($property['resource'])) {
                continue;
            }

            unset($content['resource']['properties'][$name]);
            $this->appendLink(
                $response,
                $name,
                $property['type'],
                $property['access'],
                $property['variants'],
                $property['resource']
            );
        }

        $response->setContent(json_encode($content));
    }

    /**
     * Add a link header to the response
     *
     * @param Response $response
     * @param string $property
     * @param string $type
     * @param array $access
     * @param array $variants
     * @param Resource $definition
     *
     * @return void
     */
    protected function appendLink(
        Response $response,
        $property,
        $type,
        array $access,
        array $variants,
        Resource $definition
    ) {
        $route = $this->routeLoader->getRoute($definition, 'options');
        $header = $response->headers->get('Link', null, false);
        $header[] = sprintf(
            '<%s>; rel="property"; name="%s"; type="%s"; access="%s"; variants="%s"',
            $this->urlGenerator->generate($route),
            $property,
            $type,
            implode('|', $access),
            implode('|', $variants)
        );

        $response->headers->add([
            'Link' => $header,
        ]);
    }
}
