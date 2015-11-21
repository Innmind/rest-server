<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\StorageCreateListener;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Event\Storage\PostCreateEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;

class StorageCreateListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $r;

    public function setUp()
    {
        $this->l = new StorageCreateListener(
            PropertyAccess::createPropertyAccessor()
        );
        $this->r = new HttpResource;
        $this->r->setDefinition(
            (new ResourceDefinition('foo'))
                ->setId('id')
        );
    }

    public function testExtractIdAccessibleByGetter()
    {
        $e = new PostCreateEvent($this->r, new Foo);
        $this->assertFalse($e->hasResourceId());
        $this->l->extractId($e);
        $this->assertTrue($e->hasResourceId());
        $this->assertSame(
            42,
            $e->getResourceId()
        );
    }

    public function testExtractProtectedId()
    {
        $e = new PostCreateEvent($this->r, new Bar);
        $this->assertFalse($e->hasResourceId());
        $this->l->extractId($e);
        $this->assertTrue($e->hasResourceId());
        $this->assertSame(
            24,
            $e->getResourceId()
        );
    }
}

class Foo {
    protected $id;

    public function getId()
    {
        return 42;
    }
}

class Bar {
    protected $id = 24;
}
