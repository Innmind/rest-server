<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PostCreateEvent;
use Innmind\Rest\Server\Resource;

class PostCreateEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEntity()
    {
        $e = new PostCreateEvent(new Resource, $o = new \stdClass);

        $this->assertSame(
            $o,
            $e->getEntity()
        );
    }
}
