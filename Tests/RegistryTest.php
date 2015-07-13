<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Definition\Collection;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testAddCollection()
    {
        $r = new Registry;
        $c = new Collection('foo');

        $this->assertFalse($r->hasCollection('foo'));
        $this->assertSame(
            $r,
            $r->addCollection($c)
        );
        $this->assertTrue($r->hasCollection('foo'));
        $this->assertSame(
            $c,
            $r->getCollection('foo')
        );
        $this->assertSame(
            ['foo' => $c],
            $r->getCollections()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown collection "foo"
     */
    public function testThrowIfUnknownCollection()
    {
        $r = new Registry;
        $r->getCollection('foo');
    }
}
