<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\OptionsListener;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\Request\Handler;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\CompilerPass\SubResourcePass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OptionsListenerTest extends \PHPUnit_Framework_TestCase
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

        $this->l = new OptionsListener(
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

    public function testResponseContent()
    {
        $definition = $this->registry
            ->getCollection('web')
            ->getResource('resource');
        $event = new ResponseEvent(
            $definition,
            $response = new Response,
            $request = new Request,
            $content = $this->handler->optionsAction($definition),
            'options'
        );
        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertSame(
            json_encode([
                'resource' => [
                    'id' => 'uuid',
                    'properties' => [
                        'uuid' => [
                            'type' => 'string',
                            'access' => ['READ'],
                            'variants' => [],
                        ],
                        'uri' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'scheme' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'host' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'domain' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'tld' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'port' => [
                            'type' => 'int',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'path' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'query' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'crawl_date' => [
                            'type' => 'date',
                            'access' => ['READ', 'CREATE', 'UPDATE'],
                            'variants' => ['date']
                        ],
                    ],
                    'meta' => [
                        'description' => 'Basic representation of a web resource',
                    ],
                ],
            ]),
            $response->getContent()
        );
        $this->assertSame(
            [
                '</bar/foo/>; rel="property"; name="sub_resource"; type="resource"; access="READ"; variants=""',
                '</web/resource/>; rel="property"; name="sub_resource_coll"; type="array"; access="READ"; variants=""',
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
            'index'
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
