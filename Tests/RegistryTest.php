<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Definition\Collection;
use Symfony\Component\Yaml\Yaml;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $r;

    public function setUp()
    {
        $this->r = new Registry;
    }

    public function testAddCollection()
    {
        $c = new Collection('foo');

        $this->assertFalse($this->r->hasCollection('foo'));
        $this->assertSame(
            $this->r,
            $this->r->addCollection($c)
        );
        $this->assertTrue($this->r->hasCollection('foo'));
        $this->assertSame(
            $c,
            $this->r->getCollection('foo')
        );
        $this->assertSame(
            ['foo' => $c],
            $this->r->getCollections()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown collection "foo"
     */
    public function testThrowIfUnknownCollection()
    {
        $this->r->getCollection('foo');
    }

    public function testLoad()
    {
        $this->assertSame(
            $this->r,
            $this->r->load(
                Yaml::parse(file_get_contents('fixtures/config.yml'))
            )
        );
        $this->assertTrue($this->r->hasCollection('web'));
        $this->assertTrue(
            $this->r->getCollection('web')->hasResource('resource')
        );
        $resource = $this->r->getCollection('web')->getResource('resource');
        $this->assertTrue($resource->hasMeta('description'));
        $this->assertTrue($resource->hasOption('class'));
        $this->assertTrue($resource->hasProperty('crawl_date'));
        $prop = $resource->getProperty('crawl_date');
        $this->assertSame(
            'date',
            $prop->getType()
        );
        $this->assertTrue($prop->hasAccess('UPDATE'));
        $this->assertTrue($prop->hasVariant('date'));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testThrowIfInvalidConfig()
    {
        $this->r->load(['foo']);
    }
}
