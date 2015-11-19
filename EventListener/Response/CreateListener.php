<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\HttpResourceInterface;
use Innmind\Rest\Server\Routing\RouteFactory;
use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Routing\RouteActions;
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
            KernelEvents::VIEW => [['buildResponse', 10]],
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
        $resource = $event->getControllerResult();

        if (
            !$request->attributes->has(RouteKeys::ACTION) ||
            $request->attributes->get(RouteKeys::ACTION) !== RouteActions::CREATE ||
            !$resource instanceof HttpResourceInterface
        ) {
            return;
        }

        $response = new Response;
        $event->setResponse($response);

        $response->setStatusCode(Response::HTTP_CREATED);
        $definition = $resource->getDefinition();
        $route = $this->routeFactory->makeName(
            $definition,
            RouteActions::GET
        );
        $response->headers->add([
            'Location' => $this->urlGenerator->generate(
                $route,
                ['id' => $resource->get($definition->getId())]
            ),
        ]);
    }
}
