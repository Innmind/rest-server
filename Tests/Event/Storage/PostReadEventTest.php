<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PostReadEvent;
use Innmind\Rest\Server\Definition\Resource;

class PostReadEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetResources()
    {
        $e = new PostReadEvent(
            new Resource('foo'),
            null,
            $s = new \SplObjectStorage
        );

        $this->assertSame(
            $s,
            $e->getResources()
        );
    }
}
