<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Formats;

class FormatsTest extends \PHPUnit_Framework_TestCase
{
    protected $f;

    public function setUp()
    {
        $this->f = new Formats;
        $this->f->add('json', 'application/json', 1);
    }

    public function testAdd()
    {
        $this->assertFalse($this->f->has('foo'));
        $this->assertSame(
            $this->f,
            $this->f->add('foo', 'foo', 42)
        );
        $this->assertTrue($this->f->has('foo'));
    }

    public function testHas()
    {
        $this->assertTrue($this->f->has('json'));
        $this->assertTrue($this->f->has('application/json'));
    }

    public function testGetName()
    {
        $this->assertSame(
            'json',
            $this->f->getName('application/json')
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown media type "foo"
     */
    public function testThrowIfTryongToGetNameOfUnknownType()
    {
        $this->f->getName('foo');
    }

    public function testGetMediaTypes()
    {
        $this->f->add('foo', 'foo', 42);
        $this->f->add('bar', 'bar', 24);
        $this->f->add('baz', 'baz', 24);

        $this->assertSame(
            ['foo', 'baz', 'bar', 'application/json'],
            $this->f->getMediaTypes()
        );
    }

    public function testGetMediaType()
    {
        $this->f->add('json', 'text/json', 42);

        $this->assertSame(
            'text/json',
            $this->f->getMediaType('json')
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage No media type found for the format "foo"
     */
    public function testThrowIfNoMediaTypeFound()
    {
        $this->f->getMediaType('foo');
    }
}
