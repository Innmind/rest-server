<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\DefinitionCompiler;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\CompilerPass\ArrayTypePass;

class DefinitionCompilerTest extends \PHPUnit_Framework_TestCase
{
    protected $dc;

    public function setUp()
    {
        $this->dc = new DefinitionCompiler;
    }

    public function testAddCompilerPass()
    {
        $this->assertSame(
            $this->dc,
            $this->dc->addCompilerPass(new ArrayTypePass)
        );
    }

    public function testProcess()
    {
        $this->dc->addCompilerPass(new ArrayTypePass);

        $this->assertSame(
            $this->dc,
            $this->dc->process(new Registry)
        );
    }
}
