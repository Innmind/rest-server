<?php

namespace Innmind\Rest\Server\Tests\CompilerPass;

use Innmind\Rest\Server\CompilerPass\SubResourcePass;
use Innmind\Rest\Server\Registry;
use Symfony\Component\Yaml\Yaml;

class SubResourcePassTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;
    protected $p;

    public function setUp()
    {
        $this->registry = new Registry;
        $this->registry->load(
            Yaml::parse(file_get_contents('fixtures/config.yml'))
        );
        $this->p = new SubResourcePass;
    }

    public function testProcess()
    {
        $this->assertSame(
            null,
            $this->p->process($this->registry)
        );
        $this->assertSame(
            $this->registry
                ->getCollection('bar')
                ->getResource('foo'),
            $this->registry
                ->getCollection('web')
                ->getResource('resource')
                ->getProperty('sub_resource')
                ->getOption('resource')
        );
        $this->assertSame(
            $this->registry
                ->getCollection('web')
                ->getResource('resource'),
            $this->registry
                ->getCollection('web')
                ->getResource('resource')
                ->getProperty('sub_resource_coll')
                ->getOption('resource')
        );
    }
}
