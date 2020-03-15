<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Router,
    Routing\Routes,
    Routing\Prefix,
    Identity\Identity,
    Action,
    Exception\RouteNotFound,
};
use Innmind\Url\{
    Url,
    Path,
};
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $router;
    private $directory;

    public function setUp(): void
    {
        $this->directory = require 'fixtures/mapping.php';

        $this->router = new Router(
            Routes::from($this->directory),
            new Prefix('/foo')
        );
    }

    public function testMatch()
    {
        $this->assertSame(
            $this->directory->definition('image'),
            $this->router->match(Path::of('/foo/top_dir/image/'))->definition()
        );
        $this->assertSame(
            $this->directory->definition('image'),
            $this->router->match(Path::of('/foo/top_dir/image/42'))->definition()
        );
        $this->assertEquals(
            new Identity('42'),
            $this->router->match(Path::of('/foo/top_dir/image/42'))->identity()
        );
    }

    public function testThrowWhenRouteNotFound()
    {
        $this->expectException(RouteNotFound::class);
        $this->expectExceptionMessage('/top_dir/image/');

        $this->router->match(Path::of('/top_dir/image/'));
    }

    public function testGenerate()
    {
        $path = $this->router->generate(
            Action::list(),
            $this->directory->definition('image')
        );

        $this->assertInstanceOf(Url::class, $path);
        $this->assertSame('/foo/top_dir/image/', $path->toString());

        $path = $this->router->generate(
            Action::get(),
            $this->directory->definition('image'),
            new Identity(42)
        );

        $this->assertSame('/foo/top_dir/image/42', $path->toString());
    }

    public function testGenerateWithoutPrefix()
    {
        $router = new Router(Routes::from($this->directory));

        $path = $router->generate(
            Action::get(),
            $this->directory->definition('image'),
            new Identity(42)
        );

        $this->assertSame('/top_dir/image/42', $path->toString());
    }
}
