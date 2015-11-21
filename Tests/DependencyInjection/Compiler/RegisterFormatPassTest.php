<?php

namespace Innmind\Rest\Server\Tests\DependencyInjection\Compiler;

use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterFormatPass;
use Innmind\Rest\Server\Formats;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterFormatPassTest extends \PHPUnit_Framework_TestCase
{
    protected $p;

    public function setUp()
    {
        $this->p = new RegisterFormatPass;
    }

    public function testProcess()
    {
        $b = new ContainerBuilder;
        $b->setDefinition(
            'formats',
            $c = new Definition(Formats::class)
        );
        $b->setDefinition(
            'foo',
            $d = new Definition('stdClass')
        );
        $d
            ->addTag('format', ['format' => 'json', 'mime' => 'application/json', 'priority' => '42'])
            ->addTag('format', ['format' => 'json', 'mime' => 'text/json'])
            ->addTag('foo');
        $b->setDefinition('bar', new Definition('stdClass'));

        $this->assertSame(null, $this->p->process($b));
        $calls = $c->getMethodCalls();
        $this->assertSame(2, count($calls));
        $this->assertSame(
            ['json', 'application/json', 42],
            $calls[0][1]
        );
        $this->assertSame(
            ['json', 'text/json', 0],
            $calls[1][1]
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You need to specify a format on the service foo
     */
    public function testThrowWhenNoFormatSpecified()
    {
        $b = new ContainerBuilder;
        $b->setDefinition(
            'formats',
            $c = new Definition(Formats::class)
        );
        $b->setDefinition(
            'foo',
            $d = new Definition('stdClass')
        );
        $d->addTag('format');

        $this->p->process($b);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You need to specify the associated mime type for json on foo
     */
    public function testThrowWhenNoMimeTypeSpecified()
    {
        $b = new ContainerBuilder;
        $b->setDefinition(
            'formats',
            $c = new Definition(Formats::class)
        );
        $b->setDefinition(
            'foo',
            $d = new Definition('stdClass')
        );
        $d->addTag('format', ['format' => 'json']);

        $this->p->process($b);
    }
}
