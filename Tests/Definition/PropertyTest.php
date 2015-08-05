<?php

namespace Innmind\Rest\Server\Tests\Definition;

use Innmind\Rest\Server\Definition\Property;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testSetName()
    {
        $p = new Property('foo');

        $this->assertSame(
            'foo',
            $p->getName()
        );
    }

    public function testCastToString()
    {
        $p = new Property('foo');

        $this->assertSame(
            'foo',
            (string) $p
        );
    }

    public function testSetType()
    {
        $p = new Property('foo');

        $this->assertSame(
            $p,
            $p->setType($t = 'int')
        );
        $this->assertSame(
            $t,
            $p->getType()
        );
    }

    public function testAddAccessFlagOnlyOnce()
    {
        $p = new Property('foo');

        $this->assertSame(
            $p,
            $p->addAccess('foo')
        );
        $p->addAccess('foo');
        $p->addAccess('bar');
        $this->assertSame(
            ['foo', 'bar'],
            $p->getAccess()
        );
    }

    public function testHasAccess()
    {
        $p = new Property('foo');

        $this->assertFalse($p->hasAccess('foo'));
        $p->addAccess('foo');
        $this->assertTrue($p->hasAccess('foo'));
    }

    public function testAddVariant()
    {
        $p = new Property('foo');

        $this->assertSame(
            $p,
            $p->addVariant('bar')
        );
        $this->assertSame(
            ['bar'],
            $p->getVariants()
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Property name "foo" is already used
     */
    public function testThrowIfVariantAlreadyUsed()
    {
        $p = new Property('foo');
        $p->addVariant('foo');
    }

    public function testHasVariant()
    {
        $p = new Property('foo');
        $p->addVariant('bar');

        $this->assertTrue($p->hasVariant('bar'));
        $this->assertFalse($p->hasVariant('baz'));
    }

    public function testAddOption()
    {
        $p = new Property('foo');

        $this->assertFalse($p->hasOption('foo'));
        $this->assertSame(
            $p,
            $p->addOption('foo', 'bar')
        );
        $this->assertTrue($p->hasOption('foo'));
        $this->assertSame(
            'bar',
            $p->getOption('foo')
        );
        $this->assertSame(
            ['foo' => 'bar'],
            $p->getOptions()
        );
        $p->addOption('bar', null);
        $this->assertTrue($p->hasOption('bar'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown option "bar"
     */
    public function testThrowIfUnknownOption()
    {
        $p = new Property('foo');
        $p->getOption('bar');
    }
}
