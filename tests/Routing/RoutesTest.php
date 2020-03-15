<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Routing\Routes,
    Routing\Route,
    Routing\Name,
    Routing\Match,
    Definition,
    Action,
    Identity\Identity,
    Exception\RouteNotFound,
};
use Innmind\Url\Path;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(\Iterator::class, new Routes);
    }

    public function testOf()
    {
        $directory = require 'fixtures/mapping.php';

        $routes = Routes::of(
            new Name('top_dir.image'),
            $directory->definition('image')
        );

        $this->assertInstanceOf(Routes::class, $routes);
        $this->assertCount(8, iterator_to_array($routes));
        [$list, $get, $create, $update, $remove, $link, $unlink, $options] = iterator_to_array($routes);

        $this->assertSame(Action::list(), $list->action());
        $this->assertSame('{+prefix}/top_dir/image/', $list->template()->toString());
        $this->assertSame(Action::get(), $get->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $get->template()->toString());
        $this->assertSame(Action::create(), $create->action());
        $this->assertSame('{+prefix}/top_dir/image/', $create->template()->toString());
        $this->assertSame(Action::update(), $update->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $update->template()->toString());
        $this->assertSame(Action::remove(), $remove->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $remove->template()->toString());
        $this->assertSame(Action::link(), $link->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $link->template()->toString());
        $this->assertSame(Action::unlink(), $unlink->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $unlink->template()->toString());
        $this->assertSame(Action::options(), $options->action());
        $this->assertSame('{+prefix}/top_dir/image/', $options->template()->toString());
    }

    public function testOfWithLimitedActions()
    {
        $definition = new Definition\HttpResource(
            'foo',
            new Definition\Gateway('foo'),
            new Definition\Identity('uuid'),
            Set::of(Definition\Property::class),
            Set::of(Action::class, Action::list(), Action::get())
        );

        $routes = Routes::of(
            new Name('top_dir.image'),
            $definition
        );

        $this->assertCount(3, iterator_to_array($routes));
        [$list, $get, $options] = iterator_to_array($routes);

        $this->assertSame(Action::list(), $list->action());
        $this->assertSame('{+prefix}/top_dir/image/', $list->template()->toString());
        $this->assertSame(Action::get(), $get->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $get->template()->toString());
        $this->assertSame(Action::options(), $options->action());
        $this->assertSame('{+prefix}/top_dir/image/', $options->template()->toString());
    }

    public function testFrom()
    {
        $routes = Routes::from(require 'fixtures/mapping.php');

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
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/', $resList->template()->toString());
        $this->assertSame(Action::get(), $resGet->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', $resGet->template()->toString());
        $this->assertSame(Action::create(), $resCreate->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/', $resCreate->template()->toString());
        $this->assertSame(Action::update(), $resUpdate->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', $resUpdate->template()->toString());
        $this->assertSame(Action::remove(), $resRemove->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', $resRemove->template()->toString());
        $this->assertSame(Action::link(), $resLink->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', $resLink->template()->toString());
        $this->assertSame(Action::unlink(), $resUnlink->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/{identity}', $resUnlink->template()->toString());
        $this->assertSame(Action::options(), $resOptions->action());
        $this->assertSame('{+prefix}/top_dir/sub_dir/res/', $resOptions->template()->toString());

        $this->assertSame(Action::list(), $imageList->action());
        $this->assertSame('{+prefix}/top_dir/image/', $imageList->template()->toString());
        $this->assertSame(Action::get(), $imageGet->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $imageGet->template()->toString());
        $this->assertSame(Action::create(), $imageCreate->action());
        $this->assertSame('{+prefix}/top_dir/image/', $imageCreate->template()->toString());
        $this->assertSame(Action::update(), $imageUpdate->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $imageUpdate->template()->toString());
        $this->assertSame(Action::remove(), $imageRemove->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $imageRemove->template()->toString());
        $this->assertSame(Action::link(), $imageLink->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $imageLink->template()->toString());
        $this->assertSame(Action::unlink(), $imageUnlink->action());
        $this->assertSame('{+prefix}/top_dir/image/{identity}', $imageUnlink->template()->toString());
        $this->assertSame(Action::options(), $imageOptions->action());
        $this->assertSame('{+prefix}/top_dir/image/', $imageOptions->template()->toString());
    }

    public function testMatch()
    {
        $directory = require 'fixtures/mapping.php';

        $routes = Routes::from($directory);

        $this->assertSame(
            $directory->definition('image'),
            $routes->match(Path::of('/top_dir/image/'))->definition()
        );
        $this->assertNull(
            $routes->match(Path::of('/top_dir/image/'))->identity()
        );
        $this->assertSame(
            $directory->definition('image'),
            $routes->match(Path::of('/top_dir/image/some-uuid-or-other-identity'))->definition()
        );
        $this->assertEquals(
            new Identity('some-uuid-or-other-identity'),
            $routes->match(Path::of('/top_dir/image/some-uuid-or-other-identity'))->identity()
        );
    }

    public function testThrowWhenRouteNotFound()
    {
        $this->expectException(RouteNotFound::class);
        $this->expectExceptionMessage('/foo');

        (new Routes)->match(Path::of('/foo'));
    }

    public function testGet()
    {
        $directory = require 'fixtures/mapping.php';

        $routes = Routes::from($directory);
        $definition = $directory->definition('image');

        $route = $routes->get(Action::list(), $definition);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(Action::list(), $route->action());
        $this->assertSame($definition, $route->definition());

        $definition = $directory->child('sub_dir')->definition('res');

        $route = $routes->get(Action::get(), $definition);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(Action::get(), $route->action());
        $this->assertSame($definition, $route->definition());
    }
}
