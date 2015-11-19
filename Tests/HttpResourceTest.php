<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\HttpResourceInterface;
use Innmind\Rest\Server\Definition\ResourceDefinition;

class HttpResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(HttpResourceInterface::class, new HttpResource);
    }

    public function testSetProperty()
    {
        $r = new HttpResource;

        $this->assertFalse($r->has('foo'));
        $this->assertSame(
            $r,
            $r->set('foo', 'bar')
        );
        $this->assertTrue($r->has('foo'));
        $this->assertSame(
            'bar',
            $r->get('foo')
        );
    }

    public function testSetDefinition()
    {
        $r = new HttpResource;
        $d = new ResourceDefinition('foo');

        $this->assertFalse($r->hasDefinition());
        $this->assertSame(
            $r,
            $r->setDefinition($d)
        );
        $this->assertTrue($r->hasDefinition());
        $this->assertSame(
            $d,
            $r->getDefinition()
        );
    }
}
