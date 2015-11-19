<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PostUpdateEvent;
use Innmind\Rest\Server\HttpResource;

class PostUpdateEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $r;

    public function setUp()
    {
        $this->e = new PostUpdateEvent(
            $this->r = new HttpResource,
            42,
            new \stdClass
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

    public function testGetEntity()
    {
        $this->assertInstanceOf(
            'stdClass',
            $this->e->getEntity()
        );
    }
}
