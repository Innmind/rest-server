<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Router,
    Routing\Routes,
    Routing\Prefix,
    Definition\Loader\YamlLoader,
    Definition\Types,
    Identity\Identity,
    Action,
    Exception\RouteNotFound,
};
use Innmind\Url\{
    UrlInterface,
    Path,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $router;
    private $directories;

    public function setUp()
    {
        $this->directories = (new YamlLoader(new Types))->load(
            Set::of('string', 'fixtures/mapping.yml')
        );

        $this->router = new Router(
            Routes::from($this->directories),
            new Prefix('/foo')
        );
    }

    public function testMatch()
    {
        $this->assertSame(
            $this->directories->get('top_dir')->definition('image'),
            $this->router->match(new Path('/foo/top_dir/image/'))
        );
        $this->assertSame(
            $this->directories->get('top_dir')->definition('image'),
            $this->router->match(new Path('/foo/top_dir/image/42'))
        );
    }

    public function testThrowWhenRouteNotFound()
    {
        $this->expectException(RouteNotFound::class);
        $this->expectExceptionMessage('/top_dir/image/');

        $this->router->match(new Path('/top_dir/image/'));
    }

    public function testGenerate()
    {
        $path = $this->router->generate(
            Action::list(),
            $this->directories->get('top_dir')->definition('image')
        );

        $this->assertInstanceOf(UrlInterface::class, $path);
        $this->assertSame('/foo/top_dir/image/', (string) $path);

        $path = $this->router->generate(
            Action::get(),
            $this->directories->get('top_dir')->definition('image'),
            new Identity(42)
        );

        $this->assertSame('/foo/top_dir/image/42', (string) $path);
    }

    public function testGenerateWithoutPrefix()
    {
        $router = new Router(Routes::from($this->directories));

        $path = $router->generate(
            Action::get(),
            $this->directories->get('top_dir')->definition('image'),
            new Identity(42)
        );

        $this->assertSame('/top_dir/image/42', (string) $path);
    }
}
