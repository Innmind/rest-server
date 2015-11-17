<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PreUpdateEvent;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\ResourceDefinition;

class PreUpdateEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $r;

    public function setUp()
    {
        $this->e = new PreUpdateEvent(
            $this->r = new Resource,
            42
        );
    }

    public function testGetResource()
    {
        $this->assertSame(
            $this->r,
            $this->e->getResource()
        );
    }

    public function testReplaceResource()
    {
        $r = new Resource;
        $r->setDefinition(new ResourceDefinition('foo'));

        $this->assertSame(
            $this->e,
            $this->e->replaceResource($r)
        );
        $this->assertSame(
            $r,
            $this->e->getResource()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A resource must have a definition
     */
    public function testThrowWhenReplacingAResourceWithoutDefinition()
    {
        $this->e->replaceResource(new Resource);
    }

    public function testGetResourceId()
    {
        $this->assertSame(
            42,
            $this->e->getResourceId()
        );
    }
}
