<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Loader;

use Innmind\Rest\Server\Definition\{
    Loader\YamlLoader,
    Directory,
    Access,
    Type\StringType,
    Loader,
};
use Innmind\Immutable\MapInterface;
use PHPUnit\Framework\TestCase;

class YamlLoaderTest extends TestCase
{
    public function testInterface()
    {
        $loader = new YamlLoader;

        $this->assertInstanceOf(Loader::class, $loader);
    }

    public function testLoad()
    {
        $load = new YamlLoader;

        $directories = $load('fixtures/mapping.yml');

        $this->assertInstanceOf(MapInterface::class, $directories);
        $this->assertSame('string', (string) $directories->keyType());
        $this->assertSame(Directory::class, (string) $directories->valueType());
        $this->assertSame(1, $directories->count());
        $dir = $directories->get('top_dir');
        $this->assertSame('top_dir', (string) $dir->name());
        $image = $dir->definition('image');
        $this->assertSame('image', (string) $image->name());
        $this->assertSame('uuid', (string) $image->identity());
        $this->assertSame('command', (string) $image->gateway());
        $this->assertTrue($image->isRangeable());
        $uuid = $image->properties()->get('uuid');
        $this->assertSame('uuid', $uuid->name());
        $this->assertInstanceOf(StringType::class, $uuid->type());
        $this->assertTrue($uuid->access()->matches(
            new Access(Access::READ)
        ));
        $this->assertSame(0, $uuid->variants()->size());
        $this->assertFalse($uuid->isOptional());
        $url = $image->properties()->get('url');
        $this->assertSame('url', $url->name());
        $this->assertInstanceOf(StringType::class, $url->type());
        $this->assertTrue($url->access()->matches(
            new Access(
                Access::READ,
                Access::CREATE,
                Access::UPDATE
            )
        ));
        $this->assertSame(0, $url->variants()->size());
        $this->assertFalse($url->isOptional());
        $res = $dir->flatten()->get('top_dir.sub_dir.res');
        $this->assertSame('res', (string) $res->name());
        $this->assertSame('uuid', (string) $res->identity());
        $this->assertSame('command', (string) $res->gateway());
        $this->assertFalse($res->isRangeable());
        $uuid = $res->properties()->get('uuid');
        $this->assertSame('uuid', $uuid->name());
        $this->assertInstanceOf(StringType::class, $uuid->type());
        $this->assertTrue($uuid->access()->matches(
            new Access(Access::READ)
        ));
        $this->assertSame(0, $uuid->variants()->size());
        $this->assertFalse($uuid->isOptional());
    }
}
