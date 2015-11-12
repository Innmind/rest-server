<?php

namespace Innmind\Rest\Server\Tests\DependencyInjection\Compiler;

use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterDefinitionCompilerPass;
use Innmind\Rest\Server\DefinitionCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterDefinitionCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    protected $p;

    public function setUp()
    {
        $this->p = new RegisterDefinitionCompilerPass;
    }

    public function testProcess()
    {
        $b = new ContainerBuilder;
        $b->setDefinition(
            'definition_compiler',
            $c = new Definition(DefinitionCompiler::class)
        );
        $b->setDefinition(
            'foo',
            $d = new Definition('stdClass')
        );
        $d
            ->addTag('definition.pass')
            ->addTag('definition.pass')
            ->addTag('foo');

        $this->assertSame(null, $this->p->process($b));
        $this->assertSame(1, count($c->getMethodCalls()));
    }
}
