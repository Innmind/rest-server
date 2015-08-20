<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\ResourceListener;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\Request\Handler;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\CompilerPass\SubResourcePass;
use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Serializer\Serializer;

class ResourceListenerTest extends \PHPUnit_Framework_TestCase
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

        $resourceBuilder = new ResourceBuilder(
            PropertyAccess::createPropertyAccessor(),
            $dispatcher
        );

        $this->l = new ResourceListener(
            $generator,
            $loader,
            new Serializer(
                [new ResourceNormalizer($resourceBuilder)],
                [new JsonEncoder]
            )
        );
        $this->handler = new Handler(
            new Storages,
            $resourceBuilder
        );
    }

    public function testResponseContent()
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
        $sub = new Resource;
        $sub
            ->setDefinition($this->registry->getCollection('bar')->getResource('foo'))
            ->set('uuid', 24)
            ->set('foo', 'bar');
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
            ->set('sub_resource', $sub)
            ->set('sub_resource_coll', []);
        $event = new ResponseEvent(
            $definition,
            $response = new Response,
            $request = new Request,
            $r,
            'get'
        );
        $request->attributes->set('_requested_format', 'json');
        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertSame(
            json_encode([
                'uri' => 'http://localhost',
                'scheme' => 'http',
                'host' => 'localhost',
                'domain' => 'localhost',
                'tld' => null,
                'port' => 80,
                'path' => '/',
                'query' => null,
                'crawl_date' => new \DateTime,
                'uuid' => 42,
            ]),
            $response->getContent()
        );
        $this->assertSame(
            [
                '</bar/foo/24>; rel="property"; name="sub_resource"',
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
