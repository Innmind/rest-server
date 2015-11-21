<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Routing\RouteFactory;
use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Routing\RouteActions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class OptionsListener implements EventSubscriberInterface
{
    protected $urlGenerator;
    protected $routeFactory;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RouteFactory $routeFactory
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routeFactory = $routeFactory;
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
     * Build the response for an OPTIONS request
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
            $request->attributes->get(RouteKeys::ACTION) !== RouteActions::OPTIONS
        ) {
            return;
        }

        $content = $event->getControllerResult();
        $response = new Response;
        $event->setResponse($response);

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
     * @param ResourceDefinition $definition
     *
     * @return void
     */
    protected function appendLink(
        Response $response,
        $property,
        $type,
        array $access,
        array $variants,
        $optional,
        ResourceDefinition $definition
    ) {
        $route = $this->routeFactory->makeName($definition, RouteActions::OPTIONS);
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
