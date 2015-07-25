<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PostUpdateEvent;
use Innmind\Rest\Server\Resource;

class PostUpdateEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $r;

    public function setUp()
    {
        $this->e = new PostUpdateEvent(
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

    public function testGetResourceId()
    {
        $this->assertSame(
            42,
            $this->e->getResourceId()
        );
    }
}
