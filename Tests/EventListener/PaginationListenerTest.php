<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\PaginationListener;
use Innmind\Rest\Server\Paginator;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Routing\RouteFinder;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Collection as ResourceCollection;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

class PaginationListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $rs;

    public function setUp()
    {
        $generator = $this
            ->getMockBuilder(UrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $generator
            ->method('generate')
            ->will($this->returnCallback(function($route, $params) {
                return sprintf(
                    'http://example.com/bar/foo/?%s',
                    http_build_query($params)
                );
            }));

        $this->l = new PaginationListener(
            $this->rs = new RequestStack,
            $generator,
            new RouteFinder,
            new Paginator
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                Events::NEO4J_READ_QUERY_BUILDER => 'paginateNeo4j',
                Events::DOCTRINE_READ_QUERY_BUILDER => 'paginateDoctrine',
                Events::RESPONSE => 'addPageLinks',
            ],
            PaginationListener::getSubscribedEvents()
        );
    }

    public function testAddPageLinks()
    {
        $def = new Definition('foo');
        $coll = new Collection('bar');
        $coll
            ->setStorage('foo')
            ->addResource($def);

        $response = new Response;
        $event = new ResponseEvent(
            $def,
            $response,
            $req = new Request,
            $resources = new ResourceCollection,
            'index'
        );
        $resources[] = new Resource;
        $resources[] = new Resource;
        $this->rs->push($req);
        $req->attributes->set('_rest_resource', $def);

        $this->assertSame(null, $this->l->addPageLinks($event));
        $links = $response->headers->get('Link', null, false);
        $this->assertSame(0, count($links));

        $event = new ResponseEvent(
            $def,
            $response,
            $req = new Request(['offset' => 1, 'limit' => 1]),
            $resources,
            'index'
        );
        $this->rs->pop();
        $this->rs->push($req);
        $req->attributes->set('_rest_resource', $def);

        $this->l->addPageLinks($event);
        $links = $response->headers->get('Link', null, false);
        $this->assertSame(2, count($links));
        $this->assertSame(
            [
                '<http://example.com/bar/foo/?offset=0&limit=1>; rel="prev"',
                '<http://example.com/bar/foo/?offset=2&limit=1>; rel="next"',
            ],
            $links
        );

        $event = new ResponseEvent(
            $def,
            $response = new Response,
            $req = new Request,
            $resources,
            'index'
        );
        $this->rs->pop();
        $this->rs->push($req);
        $req->attributes->set('_rest_resource', $def);
        $def->addOption('paginate', 1);

        $this->l->addPageLinks($event);
        $links = $response->headers->get('Link', null, false);
        $this->assertSame(1, count($links));
        $this->assertSame(
            ['<http://example.com/bar/foo/?offset=1&limit=1>; rel="next"'],
            $links
        );

        $event = new ResponseEvent(
            $def,
            $response = new Response,
            $req = new Request,
            new ResourceCollection,
            'index'
        );
        $this->rs->pop();
        $this->rs->push($req);
        $req->attributes->set('_rest_resource', $def);
        $def->addOption('paginate', 1);

        $this->l->addPageLinks($event);
        $links = $response->headers->get('Link', null, false);
        $this->assertSame(0, count($links));
    }
}
