<?php

namespace Innmind\Rest\Server\Tests\Routing;

use Innmind\Rest\Server\Routing\RouteFinder;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Collection;

class RouteFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFind()
    {
        $r = new Resource('foo');
        $c = new Collection('bar');
        $c
            ->setStorage('foo')
            ->addResource($r);
        $f = new RouteFinder;

        $this->assertSame(
            'innmind_rest_bar_foo_index',
            $f->find($r, 'index')
        );
        $this->assertSame(
            null,
            $f->find($r, 'weird')
        );
    }
}
