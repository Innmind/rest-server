<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected $a;

    public function setUp()
    {
        $this->a = new Application(
            'fixtures/config.yml',
            getenv('CI') ? 'fixtures/services/ci.yml' : 'fixtures/services/local.yml'
        );
    }

    public function testOptionsAction()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
                'REQUEST_METHOD' => 'OPTIONS',
            ]
        );
        $request->headers->set('Accept', 'application/json');
        $response = $this->a->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame(
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
                '</bar/foo/>; rel="property"; name="sub_resource"; type="resource"; access="READ"; variants=""; optional="1"',
                '</web/resource/>; rel="property"; name="sub_resource_coll"; type="array"; access="READ"; variants=""; optional="1"',
            ],
            $response->headers->get('Link', null, false)
        );
    }

    public function testCreateAction()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode([
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
        $response = $this->a->handle($request);
        $this->assertInstanceOf(
            Response::class,
            $response
        );
        $this->assertSame(
            201,
            $response->getStatusCode()
        );
        $this->assertTrue($response->headers->has('Location'));
        $this->assertTrue((bool) preg_match(
            '/\/web\/resource\/[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}/',
            $response->headers->get('Location')
        ));
    }

    public function testCreateMultipleResources()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
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
        $response = $this->a->handle($request);
        $this->assertSame(
            300,
            $response->getStatusCode()
        );
        $this->assertTrue($response->headers->has('Link'));
        $this->assertTrue((bool) preg_match(
            '/<\/web\/resource\/.*>; rel="resource"/',
            $response->headers->get('Link', null, false)[0]
        ));
        $this->assertSame(
            '',
            $response->getContent()
        );
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function testThrowWhenUsingUnknownContentType()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ]
        );
        $request->headers->add(['Content-Type' => 'text/xml']);

        $this->a->handle($request);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException
     */
    public function testThrowWhenNoAccepetedTypeUnderstood()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
                'REQUEST_METHOD' => 'GET',
            ]
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'text/xml'
        ]);

        $this->a->handle($request);
    }

    public function testIndexAction()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
                'REQUEST_METHOD' => 'GET',
            ]
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);

        $response = $this->a->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Link'));
        $links = $response->headers->get('Link', null, false);
        $this->assertTrue((bool) preg_match(
            '/\/web\/resource\/[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}/',
            $links[0]
        ));
        $this->assertSame(
            '</web/resource/?offset=1&limit=1>; rel="next"',
            $links[1]
        );
    }

    public function testReadAction()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
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
        $response = $this->a->handle($request);
        preg_match(
            '/\/web\/resource\/(?P<uuid>[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12})/',
            $response->headers->get('Location'),
            $matches
        );

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/' . $matches['uuid'],
                'REQUEST_METHOD' => 'GET',
            ]
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->a->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $responseContent = json_decode($response->getContent(), true);
        $this->assertTrue(is_string($responseContent['resource']['uuid']));
        unset($responseContent['resource']['uuid']);
        $this->assertSame(
            $expected,
            $responseContent
        );
    }

    public function testUpdateAction()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
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
        $response = $this->a->handle($request);
        preg_match(
            '/\/web\/resource\/(?<id>.*)/',
            $response->headers->get('Location'),
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
                    '/web/resource/%s',
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
        $response = $this->a->handle($request);
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

    public function testDeleteAction()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
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
        $response = $this->a->handle($request);
        preg_match(
            '/\/web\/resource\/(?<id>.*)/',
            $response->headers->get('Location'),
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
                    '/web/resource/%s',
                    $matches['id']
                ),
                'REQUEST_METHOD' => 'DELETE',
            ]
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $response = $this->a->handle($request);
        $this->assertSame(
            204,
            $response->getStatusCode()
        );
        $this->assertSame(
            '',
            $response->getContent()
        );
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testThrowWhenViolatingResourceDefinition()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode([
                'resource' => [
                    'uuid' => 'foo',
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
        $this->a->handle($request);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testThrowWhenResourceNotFound()
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/foo',
                'REQUEST_METHOD' => 'GET',
            ]
        );
        $request->headers->add([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $this->a->handle($request);
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
