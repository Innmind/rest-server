<?php

namespace Innmind\Rest\Server\Tests\Event;

use Innmind\Rest\Server\Event\ResourceBuildEvent;
use Innmind\Rest\Server\Definition\Resource;

class ResourceBuildEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;

    public function setUp()
    {
        $this->e = new ResourceBuildEvent(['foo'], new Resource('foo'));
    }

    public function testGetData()
    {
        $this->assertSame(
            ['foo'],
            $this->e->getData()
        );
    }

    public function testReplaceData()
    {
        $this->assertSame(
            $this->e,
            $this->e->replaceData(['bar'])
        );
        $this->assertSame(
            ['bar'],
            $this->e->getData()
        );
    }

    public function testGetDefinition()
    {
        $this->assertInstanceOf(
            Resource::class,
            $this->e->getDefinition()
        );
    }
}
