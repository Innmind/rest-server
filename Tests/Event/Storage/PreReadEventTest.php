<?php

namespace Innmind\Rest\Server\Tests\Event\Storage;

use Innmind\Rest\Server\Event\Storage\PreReadEvent;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Resource;

class PreReadEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $d;

    public function setUp()
    {
        $this->e = new PreReadEvent(
            $this->d = new ResourceDefinition('foo'),
            null
        );
    }

    public function testGetDefinition()
    {
        $this->assertSame(
            $this->d,
            $this->e->getDefinition()
        );
    }

    public function testGetId()
    {
        $this->assertFalse($this->e->hasId());
        $e = new PreReadEvent($this->d, 'foo');
        $this->assertTrue($e->hasId());
        $this->assertSame(
            'foo',
            $e->getId()
        );
    }

    public function testAddResource()
    {
        $this->assertFalse($this->e->hasResources());
        $this->assertSame(
            $this->e,
            $this->e->addResource(
                $d = (new Resource)
                    ->setDefinition($this->d)
            )
        );
        $this->assertTrue($this->e->hasResources());
        $this->assertTrue($this->e->getResources()->contains($d));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A resource must have a definition
     */
    public function testThrowIfNoDefinitionAttachedToResource()
    {
        $this->e->addResource(new Resource);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A resource must have a definition
     */
    public function testThrowIfNoDefinitionAttachedToAResourceInBag()
    {
        $s = new \SplObjectStorage;
        $s->attach(new Resource);

        $this->e->useResources($s);
    }

    public function testUseResources()
    {
        $s = new \SplObjectStorage;
        $r = new Resource;
        $r->setDefinition($this->d);
        $s->attach($r);

        $this->assertSame(
            $this->e,
            $this->e->useResources($s)
        );
        $this->assertSame(
            $s,
            $this->e->getResources()
        );
    }
}
