<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\RouteFactory;
use Innmind\Rest\Server\RouteKeys;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class CreateListener implements EventSubscriberInterface
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
     * Determine the response code to set for this response
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
            $request->attributes->get(RouteKeys::ACTION) !== 'create'
        ) {
            return;
        }

        $response = new Response;
        $event->setResponse($response);

        if ($event->getControllerResult() instanceof Collection) {
            $response->setStatusCode(Response::HTTP_MULTIPLE_CHOICES);

            return;
        }

        $response->setStatusCode(Response::HTTP_CREATED);
        $resource = $event->getControllerResult();
        $definition = $resource->getDefinition();
        $route = $this->routeFactory->makeName(
            $definition,
            'get'
        );
        $response->headers->add([
            'Location' => $this->urlGenerator->generate(
                $route,
                ['id' => $resource->get($definition->getId())]
            ),
        ]);
    }
}
