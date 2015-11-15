<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\CollectionListener;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\RouteFactory;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\RouteKeys;
use Innmind\Rest\Server\CompilerPass\SubResourcePass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\kernelEvents;

class CollectionListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $routes;
    protected $registry;
    protected $k;

    public function setUp()
    {
        $dispatcher = new EventDispatcher;
        $this->registry = new Registry;
        $this->registry->load(Yaml::parse(file_get_contents('fixtures/config.yml')));
        (new SubResourcePass)->process($this->registry);
        $loader = new RouteLoader($dispatcher, $this->registry, new RouteFactory);

        if (!$this->routes) {
            $this->routes = $loader->load('.');
        }

        $request = new Request;
        $context = new RequestContext;
        $context->fromRequest($request);
        $generator = new UrlGenerator($this->routes, $context);

        $this->l = new CollectionListener(
            $generator,
            new RouteFactory
        );
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testResponseContent()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        $s = new Collection;
        $r = new Resource;
        $r
            ->setDefinition($definition)
            ->set('uuid', 42);
        $s[] = $r;
        $r = new Resource;
        $r
            ->setDefinition($definition)
            ->set('uuid', 24);
        $s[] = $r;
        $req = new Request;
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $req,
            HttpKernel::MASTER_REQUEST,
            $s
        );
        $req->attributes->set(RouteKeys::DEFINITION, $definition);
        $req->attributes->set(RouteKeys::ACTION, 'index');
        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertTrue($event->hasResponse());
        $response = $event->getResponse();
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

        $c = new Collection;
        $c[] = $r;
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $req = new Request,
            HttpKernel::MASTER_REQUEST,
            $c
        );
        $req->attributes->set(RouteKeys::DEFINITION, $definition);
        $req->attributes->set(RouteKeys::ACTION, 'create');

        $this->l->buildResponse($event);
        $this->assertTrue($event->hasResponse());
        $response = $event->getResponse();
        $this->assertSame(
            300,
            $response->getStatusCode()
        );
    }

    public function testDoesntBuildeResponse()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        $r = new Request;
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST,
            []
        );
        $r->attributes->set(RouteKeys::DEFINITION, $definition);
        $r->attributes->set(RouteKeys::ACTION, 'options');
        $this->l->buildResponse($event);
        $this->assertFalse($event->hasResponse());
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::VIEW => 'buildResponse'],
            CollectionListener::getSubscribedEvents()
        );
    }
}
