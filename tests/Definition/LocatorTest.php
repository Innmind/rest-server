<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    Locator,
    Types,
    HttpResource,
    Loader\YamlLoader
};
use Innmind\Immutable\{
    Set,
    Map
};
use PHPUnit\Framework\TestCase;

class LocatorTest extends TestCase
{
    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidDirectoryMap()
    {
        new Locator(new Map('int', 'int'));
    }

    public function testLocate()
    {
        $locator = new Locator(
            (new YamlLoader(new Types))->load(
                (new Set('string'))->add('fixtures/mapping.yml')
            )
        );

        $resource = $locator->locate('top_dir.sub_dir.res');

        $this->assertInstanceOf(HttpResource::class, $resource);
        $this->assertSame('res', $resource->name());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DefinitionNotFoundException
     */
    public function testThrowWhenResourceNotFound()
    {
        $locator = new Locator(
            (new YamlLoader(new Types))->load(
                (new Set('string'))->add('fixtures/mapping.yml')
            )
        );

        $locator->locate('unknown');
    }
}
