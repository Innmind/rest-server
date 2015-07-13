<?php

namespace Innmind\Rest\Server\Tests\Definition;

use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Definition\Property;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testSetName()
    {
        $r = new Resource('foo');

        $this->assertSame(
            'foo',
            $r->getName()
        );
    }

    public function testCastToString()
    {
        $r = new Resource('foo');

        $this->assertSame(
            'foo',
            (string) $r
        );
    }

    public function testSetId()
    {
        $r = new Resource('foo');

        $this->assertSame(
            $r,
            $r->setId('uuid')
        );
        $this->assertSame(
            'uuid',
            $r->getId()
        );
    }

    public function testAddProperty()
    {
        $r = new Resource('foo');
        $p = new Property('foo');

        $this->assertFalse($r->hasProperty('foo'));
        $this->assertSame(
            $r,
            $r->addProperty($p)
        );
        $this->assertTrue($r->hasProperty('foo'));
        $this->assertSame(
            $p,
            $r->getProperty('foo')
        );
        $this->assertSame(
            ['foo' => $p],
            $r->getProperties()
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage The property "foo" conflicts with "bar" on "foo"
     */
    public function testThrowIfPropertyNameConflictWithOtherPropertyVariant()
    {
        $r = new Resource('foo');
        $p1 = new Property('bar');
        $p1->addVariant('foo');
        $r->addProperty($p1);

        $p2 = new Property('foo');
        $r->addProperty($p2);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage The property "baz" conflicts with "bar" on "foo"
     */
    public function testThrowIfPropertyVariantConflictWithOtherPropertyVariant()
    {
        $r = new Resource('foo');
        $p1 = new Property('bar');
        $p1->addVariant('foo');
        $r->addProperty($p1);

        $p2 = new Property('baz');
        $p2->addVariant('foo');
        $r->addProperty($p2);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown property "foo" for resource "foo"
     */
    public function testThrowIfUnknownProperty()
    {
        $r = new Resource('foo');

        $r->getProperty('foo');
    }

    public function testAddMeta()
    {
        $r = new Resource('foo');

        $this->assertFalse($r->hasMeta('description'));
        $this->assertSame(
            $r,
            $r->addMeta('description', 'foo')
        );
        $this->assertTrue($r->hasMeta('description'));
        $this->assertSame(
            'foo',
            $r->getMeta('description')
        );
        $this->assertSame(
            ['description' => 'foo'],
            $r->getMetas()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown meta "foo" for resource "foo"
     */
    public function testThrowIfUnknownMeta()
    {
        $r = new Resource('foo');

        $r->getMeta('foo');
    }

    public function testSetCollection()
    {
        $r = new Resource('foo');

        $this->assertSame(
            $r,
            $r->setCollection($c = new Collection('bar'))
        );
        $this->assertSame(
            $c,
            $r->getCollection()
        );
    }

    public function testSetStorage()
    {
        $r = new Resource('foo');

        $this->assertFalse($r->hasStorage());
        $this->assertSame(
            $r,
            $r->setStorage('neo4j')
        );
        $this->assertTrue($r->hasStorage());
        $this->assertSame(
            'neo4j',
            $r->getStorage()
        );
    }
}
