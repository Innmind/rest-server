<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Setup;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Storage\Neo4jStorage;
use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Neo4j\ONM\Configuration;
use Innmind\Neo4j\ONM\EntityManagerFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetupTest extends \PHPUnit_Framework_TestCase
{
    protected $s;

    public function setUp()
    {
        $conf = Configuration::create([
            'cache' => sys_get_temp_dir(),
            'reader' => 'yaml',
            'locations' => ['fixtures/neo4j'],
        ], true);
        $em = EntityManagerFactory::make(
            [
                'host' => getenv('CI') ? 'localhost' : 'docker',
                'username' => 'neo4j',
                'password' => 'ci',
            ],
            $conf,
            $dispatcher = new EventDispatcher
        );

        $entityBuilder = new EntityBuilder(
            $accessor = PropertyAccess::createPropertyAccessor(),
            $dispatcher
        );
        $resourceBuilder = new ResourceBuilder(
            $accessor,
            $dispatcher
        );

        $neo4j = new Neo4jStorage(
            $em,
            $dispatcher,
            $entityBuilder,
            $resourceBuilder
        );
        $this->s = new Setup(
            'fixtures/config.yml',
            ['neo4j' => $neo4j],
            new Serializer(
                [new ResourceNormalizer($resourceBuilder)],
                [new JsonEncoder]
            ),
            '/api',
            [],
            $dispatcher,
            Validation::createValidator(),
            $accessor
        );
        $this->s->addFormat('json', 'application/json', 1);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function testThrowIfContentTypeIsNotAcceptable()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ]
        );
        $request->headers->add(['Content-Type' => 'text/xml']);

        $this->s->handleRequest($request);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException
     */
    public function testThrowIfAcceptHeaderCantBeFulfilled()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'GET',
            ]
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'text/xml'
        ]);

        $this->s->handleRequest($request);
    }

    public function testCreateResource()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode($expected = [
                'resource' => [
                    'uri' => 'http://innmind.io/',
                    'scheme' => 'http',
                    'host' => 'innmind',
                    'domain' => 'innmind',
                    'tld' => 'io',
                    'port' => 80,
                    'path' => '/',
                    'query' => '',
                    'crawl_date' => '2015-08-14',
                ],
            ])
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        $this->assertInstanceOf(
            Response::class,
            $response
        );
        $this->assertSame(
            201,
            $response->getStatusCode()
        );
        $responseContent = json_decode($response->getContent(), true);
        $this->assertTrue(is_string($responseContent['resource']['uuid']));
        unset($responseContent['resource']['uuid']);
        $this->assertSame(
            $expected,
            $responseContent
        );
    }

    public function testCreateResources()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode($expected = [
                'resources' => [[
                    'uri' => 'http://innmind.io/',
                    'scheme' => 'http',
                    'host' => 'innmind',
                    'domain' => 'innmind',
                    'tld' => 'io',
                    'port' => 80,
                    'path' => '/',
                    'query' => '',
                    'crawl_date' => '2015-08-14',
                ]],
            ])
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        $this->assertInstanceOf(
            Response::class,
            $response
        );
        $this->assertSame(
            201,
            $response->getStatusCode()
        );
        $this->assertSame(
            1,
            preg_match(
                '/<\/api\/web\/resource\/.*>; rel="resource"/',
                $response->headers->get('Link', null, false)[0]
            )
        );
        $this->assertSame(
            '',
            $response->getContent()
        );
    }

    public function testGetResource()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode($expected = [
                'resources' => [[
                    'uri' => 'http://innmind.io/',
                    'scheme' => 'http',
                    'host' => 'innmind',
                    'domain' => 'innmind',
                    'tld' => 'io',
                    'port' => 80,
                    'path' => '/',
                    'query' => '',
                    'crawl_date' => '2015-08-14',
                ]],
            ])
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        preg_match(
            '/<\/api\/web\/resource\/(?<id>.*)>; rel="resource"/',
            $response->headers->get('Link'),
            $matches
        );
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => sprintf(
                    '/api/web/resource/%s',
                    $matches['id']
                ),
                'REQUEST_METHOD' => 'GET',
            ]
        );
        $request->headers->add([
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        $this->assertSame(
            [
                'resource' => [
                    'uuid' => $matches['id'],
                    'uri' => 'http://innmind.io/',
                    'scheme' => 'http',
                    'host' => 'innmind',
                    'domain' => 'innmind',
                    'tld' => 'io',
                    'port' => 80,
                    'path' => '/',
                    'query' => '',
                    'crawl_date' => '2015-08-14',
                ]
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testIndex()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode($expected = [
                'resources' => [[
                    'uri' => 'http://innmind.io/',
                    'scheme' => 'http',
                    'host' => 'innmind',
                    'domain' => 'innmind',
                    'tld' => 'io',
                    'port' => 80,
                    'path' => '/',
                    'query' => '',
                    'crawl_date' => '2015-08-14',
                ]],
            ])
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $this->s->handleRequest($request);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'GET',
            ]
        );
        $request->headers->add([
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        $this->assertSame(
            200,
            $response->getStatusCode()
        );
        $this->assertSame(
            '',
            $response->getContent()
        );
        $this->assertTrue(
            $response->headers->get('Link', null, false) >= 1
        );
    }

    public function testUpdate()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode($expected = [
                'resources' => [[
                    'uri' => 'http://innmind.io/',
                    'scheme' => 'http',
                    'host' => 'innmind',
                    'domain' => 'innmind',
                    'tld' => 'io',
                    'port' => 80,
                    'path' => '/',
                    'query' => '',
                    'crawl_date' => '2015-08-14',
                ]],
            ])
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        preg_match(
            '/<\/api\/web\/resource\/(?<id>.*)>; rel="resource"/',
            $response->headers->get('Link'),
            $matches
        );

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => sprintf(
                    '/api/web/resource/%s',
                    $matches['id']
                ),
                'REQUEST_METHOD' => 'PUT',
            ],
            json_encode($expected = [
                'resource' => [
                    'crawl_date' => '2015-08-23',
                ],
            ])
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        $this->assertSame(
            200,
            $response->getStatusCode()
        );
        $content = json_decode($response->getContent(), true);
        $this->assertSame(
            '2015-08-23',
            $content['resource']['crawl_date']
        );
    }

    public function testDelete()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode($expected = [
                'resources' => [[
                    'uri' => 'http://innmind.io/',
                    'scheme' => 'http',
                    'host' => 'innmind',
                    'domain' => 'innmind',
                    'tld' => 'io',
                    'port' => 80,
                    'path' => '/',
                    'query' => '',
                    'crawl_date' => '2015-08-14',
                ]],
            ])
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        preg_match(
            '/<\/api\/web\/resource\/(?<id>.*)>; rel="resource"/',
            $response->headers->get('Link'),
            $matches
        );

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => sprintf(
                    '/api/web/resource/%s',
                    $matches['id']
                ),
                'REQUEST_METHOD' => 'DELETE',
            ]
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        $this->assertSame(
            204,
            $response->getStatusCode()
        );
        $this->assertSame(
            '',
            $response->getContent()
        );
    }

    public function testOptions()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/api/web/resource/',
                'REQUEST_METHOD' => 'OPTIONS',
            ]
        );
        $request->headers->add([
            'Accept' => 'application/json',
        ]);
        $response = $this->s->handleRequest($request);
        $this->assertSame(
            200,
            $response->getStatusCode()
        );
        $this->assertEquals(
            [
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
                            'access' =>  ['READ', 'CREATE'],
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
            ],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(
            [
                '</api/bar/foo/>; rel="property"; name="sub_resource"; type="resource"; access="READ"; variants=""',
                '</api/web/resource/>; rel="property"; name="sub_resource_coll"; type="array"; access="READ"; variants=""',
            ],
            $response->headers->get('Link', null, false)
        );
    }
}

class WebResource
{
    public $uuid;
    public $uri;
    public $scheme;
    public $host;
    public $domain;
    public $tld;
    public $port;
    public $path;
    public $query;
    public $crawl_date;
    public $sub_resource;
    public $sub_resource_coll;
}
