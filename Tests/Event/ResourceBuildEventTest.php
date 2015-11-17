<?php

namespace Innmind\Rest\Server\Tests\Event;

use Innmind\Rest\Server\Event\ResourceBuildEvent;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Resource;

class ResourceBuildEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;

    public function setUp()
    {
        $this->e = new ResourceBuildEvent(['foo'], new ResourceDefinition('foo'));
    }

    public function testGetData()
    {
        $this->assertSame(
            ['foo'],
            $this->e->getData()
        );
    }

    public function testReplaceData()
    {
        $this->assertSame(
            $this->e,
            $this->e->replaceData($o = new \stdClass)
        );
        $this->assertSame(
            $o,
            $this->e->getData()
        );
    }

    public function testGetDefinition()
    {
        $this->assertInstanceOf(
            ResourceDefinition::class,
            $this->e->getDefinition()
        );
    }

    public function testSetResource()
    {
        $r = new Resource;
        $r->setDefinition(new ResourceDefinition('foo'));

        $this->assertFalse($this->e->hasResource());
        $this->assertSame(
            $this->e,
            $this->e->setResource($r)
        );
        $this->assertTrue($this->e->hasResource());
        $this->assertSame(
            $r,
            $this->e->getResource()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A resource must have a definition
     */
    public function testThrowWhenSettingAResourceWithoutADefinition()
    {
        $this->e->setResource(new Resource);
    }
}
