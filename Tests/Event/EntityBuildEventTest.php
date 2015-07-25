<?php

namespace Innmind\Rest\Server\Tests\Event;

use Innmind\Rest\Server\Event\EntityBuildEvent;
use Innmind\Rest\Server\Resource;

class EntityBuildEventTest extends \PHPUnit_Framework_TestCase
{
    protected $ev;
    protected $r;
    protected $e;

    public function setUp()
    {
        $this->ev = new EntityBuildEvent(
            $this->r = new Resource,
            $this->e = new \stdClass
        );
    }

    public function testGetResource()
    {
        $this->assertSame(
            $this->r,
            $this->ev->getResource()
        );
    }

    public function testGetEntity()
    {
        $this->assertSame(
            $this->e,
            $this->ev->getEntity()
        );
    }
}
