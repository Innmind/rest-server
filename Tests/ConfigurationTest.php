<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Configuration;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testComputeConfig()
    {
        $config = Yaml::parse(file_get_contents('fixtures/config.yml'));
        $c = new Configuration;
        $p = new Processor;
        $expected = $config;

        foreach ($expected['collections']['web']['resources']['resource']['properties'] as &$prop) {
            if (is_string($prop)) {
                $prop = ['type' => $prop];
            }

            if (!isset($prop['options'])) {
                $prop['options'] = [];
            }

            if (!isset($prop['variants'])) {
                $prop['variants'] = [];
            }

            if (!isset($prop['access'])) {
                $prop['access'] = ['READ'];
            }
        }

        $this->assertEquals(
            $expected,
            $p->processConfiguration($c, [$config])
        );
    }
}
