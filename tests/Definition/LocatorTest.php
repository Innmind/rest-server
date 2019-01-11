<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Definition\Locator,
    Definition\HttpResource,
    Exception\DefinitionNotFound,
};
use PHPUnit\Framework\TestCase;

class LocatorTest extends TestCase
{
    public function testLocate()
    {
        $locate = new Locator(
            require 'fixtures/mapping.php'
        );

        $resource = $locate('top_dir.sub_dir.res');

        $this->assertInstanceOf(HttpResource::class, $resource);
        $this->assertSame('res', (string) $resource->name());
    }

    public function testThrowWhenResourceNotFound()
    {
        $this->expectException(DefinitionNotFound::class);

        $locate = new Locator(
            require 'fixtures/mapping.php'
        );

        $locate('unknown');
    }
}
