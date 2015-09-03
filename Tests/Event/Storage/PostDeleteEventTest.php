<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PostDeleteEvent;
use Innmind\Rest\Server\Definition\Resource;

class PostDeleteEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEntity()
    {
        $e = new PostDeleteEvent(new Resource('foo'), 42, $o = new \stdClass);

        $this->assertSame(
            $o,
            $e->getEntity()
        );
    }
}
