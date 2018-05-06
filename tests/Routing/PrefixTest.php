<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Routing\Prefix,
    Exception\LogicException,
};
use Innmind\Url\{
    PathInterface,
    Path,
};
use PHPUnit\Framework\TestCase;

class PrefixTest extends TestCase
{
    public function testOutOf()
    {
        $prefix = new Prefix('/foo/');

        $path = $prefix->outOf(new Path('/foo/bar/foo/baz/'));

        $this->assertInstanceOf(PathInterface::class, $path);
        $this->assertSame('/bar/foo/baz/', (string) $path);
    }

    public function testOutOfEmptyPrefix()
    {
        $prefix = Prefix::none();

        $this->assertInstanceOf(Prefix::class, $prefix);

        $path = $prefix->outOf($expected = new Path('/foo'));

        $this->assertSame($expected, $path);
    }

    public function testThrowWhenPrefixNotFound()
    {
        $prefix = new Prefix('/foo');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('/bar/foo/baz');

        $prefix->outOf(new Path('/bar/foo/baz'));
    }
}
