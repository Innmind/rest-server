<?php

namespace Innmind\Rest\Server\Tests\Definition;

use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Definition\Resource;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetName()
    {
        $c = new Collection('foo');

        $this->assertSame(
            'foo',
            $c->getName()
        );
    }

    public function testCastToString()
    {
        $c = new Collection('foo');

        $this->assertSame(
            'foo',
            (string) $c
        );
    }

    public function testSetStorage()
    {
        $c = new Collection('foo');

        $this->assertSame(
            $c,
            $c->setStorage('neo4j')
        );
        $this->assertSame(
            'neo4j',
            $c->getStorage()
        );
    }

    public function testAddResource()
    {
        $c = new Collection('foo');
        $r = new Resource('bar');
        $r->setStorage('foo');

        $this->assertFalse($c->hasResource('bar'));
        $this->assertSame(
            $c,
            $c->addResource($r)
        );
        $this->assertTrue($c->hasResource('bar'));
        $this->assertSame(
            $r,
            $c->getResource('bar')
        );
        $this->assertSame(
            ['bar' => $r],
            $c->getResources()
        );
    }

    public function testSetCollectionToResource()
    {
        $c = new Collection('foo');
        $r = new Resource('bar');
        $r->setStorage('foo');
        $c->addResource($r);

        $this->assertSame(
            $c,
            $r->getCollection()
        );
    }

    public function testInheritStorage()
    {
        $c = new Collection('foo');
        $c->setStorage('bar');
        $r = new Resource('foo');
        $c->addResource($r);

        $this->assertSame(
            'bar',
            $r->getStorage()
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You must define a storage for "foo"
     */
    public function testThrowIfNoStorageDefined()
    {
        $c = new Collection('foo');
        $c->addResource(new Resource('foo'));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Unknown resource "foo" in collection "bar"
     */
    public function testThrowIfUnknownResource()
    {
        $c = new Collection('bar');
        $c->getResource('foo');
    }
}
