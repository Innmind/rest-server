<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Storage\Neo4jStorage;

class StoragesTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $s = new Storages;
        $mock = $this
            ->getMockBuilder(Neo4jStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertFalse($s->has('foo'));
        $this->assertSame(
            $s,
            $s->add('foo', $mock)
        );
        $this->assertTrue($s->has('foo'));
        $this->assertSame(
            $mock,
            $s->get('foo')
        );
    }
}
