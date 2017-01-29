<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\Configuration;
use Symfony\Component\{
    Config\Definition\Processor,
    Yaml\Yaml
};

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $c = new Configuration;
        $p = new Processor;

        $result = $p->processConfiguration(
            $c,
            [$expected = Yaml::parse(file_get_contents('fixtures/mapping.yml'))]
        );

        $expected['top_dir']['resources']['image']['properties']['uuid']['access'] = ['READ'];
        $expected['top_dir']['resources']['image']['properties']['uuid']['variants'] = [];
        $expected['top_dir']['resources']['image']['properties']['uuid']['optional'] = false;
        $expected['top_dir']['resources']['image']['properties']['uuid']['options'] = [];
        $expected['top_dir']['resources']['image']['properties']['url']['variants'] = [];
        $expected['top_dir']['resources']['image']['properties']['url']['optional'] = false;
        $expected['top_dir']['resources']['image']['properties']['url']['options'] = [];
        $expected['top_dir']['resources']['image']['options'] = [];
        $expected['top_dir']['resources']['image']['metas'] = [];
        $expected['top_dir']['resources']['image']['rangeable'] = true;

        $this->assertSame($expected, $result);
    }
}
