<?php

namespace Innmind\Rest\Server\EventListener\Response;

use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Routing\RouteFactory;
use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Routing\RouteActions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

class CollectionListener implements EventSubscriberInterface
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
            KernelEvents::VIEW => [['buildResponse', 20]],
        ];
    }

    /**
     * Build the response event for a list of resources
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @return void
     */
    public function buildResponse(GetResponseForControllerResultEvent $event)
    {
        $content = $event->getControllerResult();

        if (!$content instanceof Collection) {
            return;
        }

        $links = [];

        foreach ($content as $resource) {
            $definition = $resource->getDefinition();
            $route = $this->routeFactory->makeName(
                $definition,
                RouteActions::GET
            );
            $links[] = sprintf(
                '<%s>; rel="resource"',
                $this->urlGenerator->generate(
                    $route,
                    ['id' => $resource->get($definition->getId())]
                )
            );
        }

        $response = new Response;
        $response->headers->add(['Link' => $links]);
        $request = $event->getRequest();

        if ($request->attributes->get(RouteKeys::ACTION) === RouteActions::CREATE) {
            $response->setStatusCode(Response::HTTP_MULTIPLE_CHOICES);
        }

        $event->setResponse($response);
    }
}
