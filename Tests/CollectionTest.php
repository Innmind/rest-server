<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\HttpResourceInterface;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testContains()
    {
        $c = new Collection;
        $c[] = $r = new HttpResource;
        $this->assertTrue($c->contains($r));
    }

    public function testCount()
    {
        $c = new Collection;
        $c[] = new HttpResource;

        $this->assertSame(1, count($c));
    }

    public function testArrayAccess()
    {
        $c = new Collection;
        $c[] = $r = new HttpResource;

        $this->assertTrue(isset($c[0]));
        $this->assertSame($r, $c[0]);
        unset($c[0]);
        $this->assertFalse(isset($c[0]));
    }

    public function testTraversability()
    {
        $c = new Collection;
        $c[] = $r = new HttpResource;

        foreach ($c as $resource) {
            $this->assertInstanceOf(
                HttpResourceInterface::class,
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
