<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Routing\RouteFinder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class OptionsListener implements EventSubscriberInterface
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
                isset($property['optional']) ? $property['optional'] : false,
                $property['resource']
            );
        }

        $response
            ->setContent(json_encode($content))
            ->headers
            ->set('Content-Type', 'application/json');
    }

    /**
     * Add a link header to the response
     *
     * @param Response $response
     * @param string $property
     * @param string $type
     * @param array $access
     * @param array $variants
     * @param bool $optional
     * @param Innmind\Rest\Server\Definition\Resource $definition
     *
     * @return void
     */
    protected function appendLink(
        HttpResponse $response,
        $property,
        $type,
        array $access,
        array $variants,
        $optional,
        Resource $definition
    ) {
        $route = $this->routeFinder->find($definition, 'options');
        $header = $response->headers->get('Link', null, false);
        $header[] = sprintf(
            '<%s>; rel="property"; name="%s"; type="%s"; access="%s"; variants="%s"; optional="%s"',
            $this->urlGenerator->generate($route),
            $property,
            $type,
            implode('|', $access),
            implode('|', $variants),
            (int) $optional
        );

        $response->headers->add([
            'Link' => $header,
        ]);
    }
}
