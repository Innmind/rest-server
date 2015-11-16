<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\PaginationListener;
use Innmind\Rest\Server\Paginator;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Routing\RouteFactory;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Collection as ResourceCollection;
use Innmind\Rest\Server\Resource;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class PaginationListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $rs;
    protected $k;

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
            new RouteFactory,
            new Paginator
        );
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                Events::NEO4J_READ_QUERY_BUILDER => 'paginateNeo4j',
                Events::DOCTRINE_READ_QUERY_BUILDER => 'paginateDoctrine',
                KernelEvents::RESPONSE => 'addPageLinks',
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
        $event = new FilterResponseEvent(
            $this->k,
            $req = new Request,
            HttpKernel::MASTER_REQUEST,
            $response
        );
        $req->attributes->set(RouteKeys::DEFINITION, $def);
        $req->attributes->set(RouteKeys::ACTION, 'index');
        $this->rs->push($req);

        $this->assertSame(null, $this->l->addPageLinks($event));
        $links = $response->headers->get('Link', null, false);
        $this->assertSame(0, count($links));

        $event = new FilterResponseEvent(
            $this->k,
            $req = new Request(['offset' => 1, 'limit' => 1]),
            HttpKernel::MASTER_REQUEST,
            $response
        );
        $this->rs->pop();
        $this->rs->push($req);
        $req->attributes->set(RouteKeys::DEFINITION, $def);
        $req->attributes->set(RouteKeys::ACTION, 'index');

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

        $event = new FilterResponseEvent(
            $this->k,
            $req = new Request,
            HttpKernel::MASTER_REQUEST,
            $response = new Response
        );
        $req->attributes->set(RouteKeys::DEFINITION, $def);
        $req->attributes->set(RouteKeys::ACTION, 'index');
        $this->rs->pop();
        $this->rs->push($req);
        $def->addOption('paginate', 1);

        $this->l->addPageLinks($event);
        $links = $response->headers->get('Link', null, false);
        $this->assertSame(1, count($links));
        $this->assertSame(
            ['<http://example.com/bar/foo/?offset=1&limit=1>; rel="next"'],
            $links
        );
    }
}
