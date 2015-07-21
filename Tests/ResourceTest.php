<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Resource as Definition;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testSetProperty()
    {
        $r = new Resource;

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
        $r = new Resource;
        $d = new Definition('foo');

        $this->assertSame(
            $r,
            $r->setDefinition($d)
        );
        $this->assertSame(
            $d,
            $r->getDefinition()
        );
    }
}
