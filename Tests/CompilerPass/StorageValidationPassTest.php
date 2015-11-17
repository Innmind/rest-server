<?php

namespace Innmind\Rest\Server\Tests\CompilerPass;

use Innmind\Rest\Server\CompilerPass\StorageValidationPass;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Definition\ResourceDefinition;

class StorageValidationPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Innmind\Rest\Server\Exception\UnknownStorageException
     * @expectedExceptionMessage Unknown storage "foo" for foo::bar
     */
    public function testThrowWhenUnknownStorageFound()
    {
        $r = new Registry;
        $c = new Collection('foo');
        $d = new ResourceDefinition('bar');
        $d->setStorage('foo');
        $c->addResource($d);
        $r->addCollection($c);

        $p = new StorageValidationPass(new Storages);

        $p->process($r);
    }

    public function testProcess()
    {
        $p = new StorageValidationPass(new Storages);

        $this->assertSame(
            null,
            $p->process(new Registry)
        );
    }
}
