<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\CollectionListener;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\Request\Handler;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\CompilerPass\SubResourcePass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validation;

class CollectionListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $routes;
    protected $registry;
    protected $handler;

    public function setUp()
    {
        $dispatcher = new EventDispatcher;
        $this->registry = new Registry;
        $this->registry->load(Yaml::parse(file_get_contents('fixtures/config.yml')));
        (new SubResourcePass)->process($this->registry);
        $loader = new RouteLoader($dispatcher, $this->registry);

        if (!$this->routes) {
            $this->routes = $loader->load('.');
        }

        $request = new Request;
        $context = new RequestContext;
        $context->fromRequest($request);
        $generator = new UrlGenerator($this->routes, $context);

        $this->l = new CollectionListener(
            $generator,
            $loader
        );
        $this->handler = new Handler(
            new Storages,
            new ResourceBuilder(
                PropertyAccess::createPropertyAccessor(),
                Validation::createValidator(),
                $dispatcher
            )
        );
    }

    public function testResponseContent()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        $s = new \SplObjectStorage;
        $r = new Resource;
        $r
            ->setDefinition($definition)
            ->set('uuid', 42);
        $s->attach($r);
        $r = new Resource;
        $r
            ->setDefinition($definition)
            ->set('uuid', 24);
        $s->attach($r);
        $event = new ResponseEvent(
            $definition,
            $response = new Response,
            $request = new Request,
            $s,
            'index'
        );
        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertSame(
            '',
            $response->getContent()
        );
        $this->assertSame(
            [
                '</web/resource/42>; rel="resource"',
                '</web/resource/24>; rel="resource"',
            ],
            $response->headers->get('Link', null, false)
        );
    }

    public function testDoesntBuildeResponse()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        $event = new ResponseEvent(
            $definition,
            $response = new Response,
            $request = new Request,
            [],
            'options'
        );
        $this->l->buildResponse($event);
        $this->assertSame(
            '',
            $response->getContent()
        );
        $this->assertSame(
            [],
            $response->headers->get('Link', null, false)
        );
    }
}
