<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PreDeleteEvent;
use Innmind\Rest\Server\Definition\Resource;

class PreDeleteEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $d;

    public function setUp()
    {
        $this->e = new PreDeleteEvent(
            $this->d = new Resource('foo'),
            42
        );
    }

    public function testGetDefinition()
    {
        $this->assertSame(
            $this->d,
            $this->e->getDefinition()
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
