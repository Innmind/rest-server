<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Resource;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testContains()
    {
        $c = new Collection;
        $c[] = $r = new Resource;
        $this->assertTrue($c->contains($r));
    }

    public function testCount()
    {
        $c = new Collection;
        $c[] = new Resource;

        $this->assertSame(1, count($c));
    }

    public function testArrayAccess()
    {
        $c = new Collection;
        $c[] = $r = new Resource;

        $this->assertTrue(isset($c[0]));
        $this->assertSame($r, $c[0]);
        unset($c[0]);
        $this->assertFalse(isset($c[0]));
    }

    public function testTraversability()
    {
        $c = new Collection;
        $c[] = $r = new Resource;

        foreach ($c as $resource) {
            $this->assertInstanceOf(
                Resource::class,
                $resource
            );
        }

        $this->assertFalse($c->valid());
        $c->rewind();
        $this->assertTrue($c->valid());
        $this->assertSame(0, $c->key());
        $this->assertSame($r, $c->current());
    }
}
