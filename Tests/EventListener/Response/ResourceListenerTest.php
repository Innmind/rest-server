<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\ResourceListener;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Routing\RouteLoader;
use Innmind\Rest\Server\Routing\RouteFactory;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\Routing\RouteKeys;
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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ResourceListenerTest extends \PHPUnit_Framework_TestCase
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

        $resourceBuilder = new ResourceBuilder(
            PropertyAccess::createPropertyAccessor(),
            $dispatcher
        );

        $formats = new Formats;
        $formats->add('json', 'application/json', 1);

        $this->l = new ResourceListener(
            $generator,
            new RouteFactory,
            new Serializer(
                [new ResourceNormalizer($resourceBuilder)],
                [new JsonEncoder]
            ),
            $formats
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
        if (!$definition->hasProperty('uuid')) {
            $definition->addProperty(
                (new Property('uuid'))
                    ->setType('string')
                    ->addAccess('READ')
            );
        }
        $sub = new HttpResource;
        $sub
            ->setDefinition($this->registry->getCollection('bar')->getResource('foo'))
            ->set('uuid', 24)
            ->set('foo', 'bar');
        $r = new HttpResource;
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
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $request = new Request,
            HttpKernel::MASTER_REQUEST,
            $r
        );
        $request->attributes->set(RouteKeys::DEFINITION, $definition);
        $request->attributes->set(RouteKeys::ACTION, 'get');
        $request->setRequestFormat('json');

        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertTrue($event->hasResponse());
        $response = $event->getResponse();
        $this->assertSame(
            json_encode([
                'resource' => [
                    'uuid' => 42,
                    'uri' => 'http://localhost',
                    'scheme' => 'http',
                    'host' => 'localhost',
                    'domain' => 'localhost',
                    'tld' => null,
                    'port' => 80,
                    'path' => '/',
                    'query' => null,
                    'crawl_date' => new \DateTime,
                ],
            ]),
            $response->getContent()
        );
        $this->assertSame(
            [
                '</bar/foo/24>; rel="property"; name="sub_resource"',
            ],
            $response->headers->get('Link', null, false)
        );
        $this->assertSame(
            'application/json',
            $response->headers->get('Content-Type')
        );
    }

    public function testDoesntBuildeResponse()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $request = new Request,
            HttpKernel::MASTER_REQUEST,
            []
        );
        $request->attributes->set(RouteKeys::DEFINITION, $definition);
        $request->attributes->set(RouteKeys::ACTION, 'options');

        $this->l->buildResponse($event);
        $this->assertFalse($event->hasResponse());
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::VIEW => 'buildResponse'],
            ResourceListener::getSubscribedEvents()
        );
    }
}
