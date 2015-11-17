<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PostReadEvent;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Collection;

class PostReadEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetResources()
    {
        $e = new PostReadEvent(
            new ResourceDefinition('foo'),
            null,
            $s = new Collection
        );

        $this->assertSame(
            $s,
            $e->getResources()
        );
    }
}
