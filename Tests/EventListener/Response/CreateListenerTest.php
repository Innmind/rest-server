<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\CreateListener;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\RouteFactory;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\RouteKeys;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\CompilerPass\SubResourcePass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class CreateListenerTest extends \PHPUnit_Framework_TestCase
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

        $this->l = new CreateListener(
            $generator,
            new RouteFactory
        );
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testResponse()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        if (!$definition->hasProperty('uuid')) {
            $definition->addProperty(
                (new Property('uuid'))
                    ->setType('string')
                    ->addAccess('READ')
            );
        }

        $r = new Resource;
        $r
            ->setDefinition($definition)
            ->set('uuid', 42)
            ->set('uri', 'http://localhost')
            ->set('scheme', 'http')
            ->set('host', 'localhost')
            ->set('domain', 'localhost')
            ->set('tld', null)
            ->set('port', 80)
            ->set('path', '/')
            ->set('query', null)
            ->set('crawl_date', new \DateTime)
            ->set('sub_resource', null)
            ->set('sub_resource_coll', []);
        $req = new Request;
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $req,
            HttpKernel::MASTER_REQUEST,
            $r
        );
        $req->attributes->set(RouteKeys::DEFINITION, $definition);
        $req->attributes->set(RouteKeys::ACTION, 'create');
        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertTrue($event->hasResponse());
        $response = $event->getResponse();
        $this->assertSame(
            201,
            $response->getStatusCode()
        );
        $this->assertSame(
            '/web/resource/42',
            $response->headers->get('Location')
        );
    }

    public function testDoesntBuildeResponse()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        $req = new Request;
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $req,
            HttpKernel::class,
            []
        );
        $req->attributes->set(RouteKeys::DEFINITION, $definition);
        $req->attributes->set(RouteKeys::ACTION, 'options');
        $this->l->buildResponse($event);
        $this->assertFalse($event->hasResponse());
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::VIEW => [['buildResponse', 10]]],
            CreateListener::getSubscribedEvents()
        );
    }
}
