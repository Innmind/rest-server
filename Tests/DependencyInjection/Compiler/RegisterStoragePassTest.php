<?php

namespace Innmind\Rest\Server\Tests\DependencyInjection\Compiler;

use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterStoragePass;
use Innmind\Rest\Server\Storages;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterStoragePassTest extends \PHPUnit_Framework_TestCase
{
    protected $p;

    public function setUp()
    {
        $this->p = new RegisterStoragePass;
    }

    public function testProcess()
    {
        $b = new ContainerBuilder;
        $b->setDefinition(
            'storages',
            $c = new Definition(Storages::class)
        );
        $b->setDefinition(
            'foo',
            $d = new Definition('stdClass')
        );
        $d
            ->addTag('storage', ['alias' => 'bar'])
            ->addTag('foo');
        $b->setDefinition('bar', new Definition('stdClass'));

        $this->assertSame(null, $this->p->process($b));
        $this->assertSame(1, count($c->getMethodCalls()));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You must specify an alias for the storage foo
     */
    public function testThrowWhenNoAliasSpecifiedOnStorage()
    {
        $b = new ContainerBuilder;
        $b->setDefinition(
            'storages',
            $c = new Definition(Storages::class)
        );
        $b->setDefinition(
            'foo',
            $d = new Definition('stdClass')
        );
        $d->addTag('storage');

        $this->p->process($b);
    }
}
