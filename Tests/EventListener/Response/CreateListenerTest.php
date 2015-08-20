<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\CreateListener;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\Request\Handler;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\CompilerPass\SubResourcePass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validation;

class CreateListenerTest extends \PHPUnit_Framework_TestCase
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

        $this->l = new CreateListener(
            $generator,
            $loader
        );
        $this->handler = new Handler(
            new Storages,
            new ResourceBuilder(
                PropertyAccess::createPropertyAccessor(),
                $dispatcher
            )
        );
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
        $event = new ResponseEvent(
            $definition,
            $response = new Response,
            $request = new Request,
            $r,
            'create'
        );
        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
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
        $event = new ResponseEvent(
            $definition,
            $response = new Response,
            $request = new Request,
            [],
            'options'
        );
        $this->l->buildResponse($event);
        $this->assertSame(
            200,
            $response->getStatusCode()
        );
        $this->assertSame(
            null,
            $response->headers->get('Link')
        );
    }
}
