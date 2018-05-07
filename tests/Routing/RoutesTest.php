<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Routing\Routes,
    Routing\Route,
    Routing\Name,
    Routing\Match,
    Definition\Loader\YamlLoader,
    Definition,
    Action,
    Identity\Identity,
    Exception\RouteNotFound,
};
use Innmind\Url\Path;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(\Iterator::class, new Routes);
    }

    public function testOf()
    {
        $directories = (new YamlLoader)('fixtures/mapping.yml');

        $routes = Routes::of(
            new Name('top_dir.image'),
            $directories->get('top_dir')->definition('image')
        );

        $this->assertInstanceOf(Routes::class, $routes);
        $this->assertCount(8, iterator_to_array($routes));
        [$list, $get, $create, $update, $remove, $link, $unlink, $options] = iterator_to_array($routes);

        $this->assertSame(Action::list(), $list->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $list->template());
        $this->assertSame(Action::get(), $get->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $get->template());
        $this->assertSame(Action::create(), $create->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $create->template());
        $this->assertSame(Action::update(), $update->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $update->template());
        $this->assertSame(Action::remove(), $remove->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $remove->template());
        $this->assertSame(Action::link(), $link->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $link->template());
        $this->assertSame(Action::unlink(), $unlink->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $unlink->template());
        $this->assertSame(Action::options(), $options->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $options->template());
    }

    public function testOfWithLimitedActions()
    {
        $definition = new Definition\HttpResource(
            'foo',
            new Definition\Identity('uuid'),
            new Map('string', Definition\Property::class),
            (new Map('scalar', 'variable'))
                ->put('actions', ['list', 'get']),
            new Map('scalar', 'variable'),
            new Definition\Gateway('foo'),
            false,
            new Map('string', 'string')
        );

        $routes = Routes::of(
            new Name('top_dir.image'),
            $definition
        );

        $this->assertCount(3, iterator_to_array($routes));
        [$list, $get, $options] = iterator_to_array($routes);

        $this->assertSame(Action::list(), $list->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $list->template());
        $this->assertSame(Action::get(), $get->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $get->template());
        $this->assertSame(Action::options(), $options->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $options->template());
    }

    public function testFrom()
    {
        $directories = (new YamlLoader)('fixtures/mapping.yml');

        $routes = Routes::from($directories);

        $this->assertInstanceOf(Routes::class, $routes);
        $this->assertCount(16, iterator_to_array($routes));
        [
            $imageList,
            $imageGet,
            $imageCreate,
            $imageUpdate,
            $imageRemove,
            $imageLink,
            $imageUnlink,
            $imageOptions,
            $resList,
            $resGet,
            $resCreate,
            $resUpdate,
            $resRemove,
            $resLink,
            $resUnlink,
            $resOptions
        ] = iterator_to_array($routes);

        $this->assertSame(Action::list(), $resList->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/', (string) $resList->template());
        $this->assertSame(Action::get(), $resGet->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', (string) $resGet->template());
        $this->assertSame(Action::create(), $resCreate->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/', (string) $resCreate->template());
        $this->assertSame(Action::update(), $resUpdate->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', (string) $resUpdate->template());
        $this->assertSame(Action::remove(), $resRemove->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', (string) $resRemove->template());
        $this->assertSame(Action::link(), $resLink->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', (string) $resLink->template());
        $this->assertSame(Action::unlink(), $resUnlink->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', (string) $resUnlink->template());
        $this->assertSame(Action::options(), $resOptions->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/', (string) $resOptions->template());

        $this->assertSame(Action::list(), $imageList->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $imageList->template());
        $this->assertSame(Action::get(), $imageGet->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $imageGet->template());
        $this->assertSame(Action::create(), $imageCreate->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $imageCreate->template());
        $this->assertSame(Action::update(), $imageUpdate->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $imageUpdate->template());
        $this->assertSame(Action::remove(), $imageRemove->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $imageRemove->template());
        $this->assertSame(Action::link(), $imageLink->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $imageLink->template());
        $this->assertSame(Action::unlink(), $imageUnlink->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', (string) $imageUnlink->template());
        $this->assertSame(Action::options(), $imageOptions->action());
        $this->assertSame('{+prefix}/top_dir/image/', (string) $imageOptions->template());
    }

    public function testMatch()
    {
        $directories = (new YamlLoader)('fixtures/mapping.yml');

        $routes = Routes::from($directories);

        $this->assertSame(
            $directories->get('top_dir')->definition('image'),
            $routes->match(new Path('/top_dir/image/'))->definition()
        );
        $this->assertNull(
            $routes->match(new Path('/top_dir/image/'))->identity()
        );
        $this->assertSame(
            $directories->get('top_dir')->definition('image'),
            $routes->match(new Path('/top_dir/image/some-uuid-or-other-identity'))->definition()
        );
        $this->assertEquals(
            new Identity('some-uuid-or-other-identity'),
            $routes->match(new Path('/top_dir/image/some-uuid-or-other-identity'))->identity()
        );
    }

    public function testThrowWhenRouteNotFound()
    {
        $this->expectException(RouteNotFound::class);
        $this->expectExceptionMessage('/foo');

        (new Routes)->match(new Path('/foo'));
    }

    public function testGet()
    {
        $directories = (new YamlLoader)('fixtures/mapping.yml');

        $routes = Routes::from($directories);
        $definition = $directories->get('top_dir')->definition('image');

        $route = $routes->get(Action::list(), $definition);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(Action::list(), $route->action());
        $this->assertSame($definition, $route->definition());

        $definition = $directories->get('top_dir')->child('sub_dir')->definition('res');

        $route = $routes->get(Action::get(), $definition);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(Action::get(), $route->action());
        $this->assertSame($definition, $route->definition());
    }
}
