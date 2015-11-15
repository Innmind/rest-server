<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\RouteKeys;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Definition\Resource;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class RequestListener implements EventSubscriberInterface
{
    protected $registry;
    protected $parser;

    public function __construct(Registry $registry, Parser $parser)
    {
        $this->registry = $registry;
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['determineFormat', -10],
                ['computeDefinition', 20],
            ],
        ];
    }

    /**
     * Replace definitions paths ({collection}::{resource}) notation to
     * the actual resource definition
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function computeDefinition(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return;
        }

        $definition = $request->attributes->get(RouteKeys::DEFINITION);

        if ($definition instanceof Resource) {
            return;
        }

        list($collection, $resource) = explode('::', $definition);
        $request->attributes->set(
            RouteKeys::DEFINITION,
            $this
                ->registry
                ->getCollection($collection)
                ->getResource($resource)
        );
    }

    /**
     * Verify the content type and accepted one can be understood
     *
     * @param GetResponseEvent $event
     *
     * @throws UnsupportedMediaTypeHttpException If the content type is not supported
     * @throws NotAcceptableHttpException If the wished type is not supported
     *
     * @return void
     */
    public function determineFormat(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return;
        }

        $action = $request->attributes->get(RouteKeys::ACTION);

        if (
            in_array($action, ['create', 'update']) &&
            !$this->parser->isContentTypeAcceptable($request)
        ) {
            throw new UnsupportedMediaTypeHttpException;
        }

        if (!$this->parser->isRequestedTypeAcceptable($request)) {
            throw new NotAcceptableHttpException;
        }

        $request->setRequestFormat(
            $this->parser->getRequestedFormat($request)
        );
    }
}
