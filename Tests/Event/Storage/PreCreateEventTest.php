<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PreCreateEvent;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\ResourceDefinition;

class PreCreateEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $r;

    public function setUp()
    {
        $this->e = new PreCreateEvent($this->r = new Resource);
    }

    public function testGetResource()
    {
        $this->assertSame(
            $this->r,
            $this->e->getResource()
        );
    }

    public function testReplaceEvent()
    {
        $r = new Resource;
        $r->setDefinition(new ResourceDefinition('foo'));

        $this->assertSame(
            $this->e,
            $this->e->replaceResource($r)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A resource must have a definition
     */
    public function testThrowIfReplacingResourceWithoutDefinition()
    {
        $this->e->replaceResource(new Resource);
    }

    public function testSetResourceId()
    {
        $this->assertFalse($this->e->hasResourceId());
        $this->assertSame(
            null,
            $this->e->getResourceId()
        );
        $this->assertSame(
            $this->e,
            $this->e->setResourceId('42')
        );
        $this->assertTrue($this->e->hasResourceId());
        $this->assertSame(
            '42',
            $this->e->getResourceId()
        );
    }
}
