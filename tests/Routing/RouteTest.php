<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Routing\Route,
    Routing\Name,
    Definition,
    Action,
    Identity\Identity,
};
use Innmind\UrlTemplate\Template;
use Innmind\Url\Path;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    private $definition;

    public function setUp(): void
    {
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Gateway('foo'),
            new Definition\Identity('uuid'),
            Set::of(Definition\Property::class)
        );
    }

    public function testInterface()
    {
        $route = new Route(
            Action::get(),
            $template = Template::of('/foo'),
            $name = new Name('foo'),
            $this->definition
       );

        $this->assertSame(Action::get(), $route->action());
        $this->assertSame($template, $route->template());
        $this->assertSame($name, $route->name());
        $this->assertSame($this->definition, $route->definition());
    }

    /**
     * @dataProvider cases
     */
    public function testOf($action, $expected)
    {
        $route = Route::of(
            $action,
            new Name('foo.bar.baz'),
            $this->definition
        );

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($expected, $route->template()->toString());
    }

    public function testMatches()
    {
        $route = Route::of(
            Action::list(),
            new Name('foo.bar.baz'),
            $this->definition
        );

        $this->assertTrue($route->matches(Path::of('/foo/bar/baz/')));
        $this->assertFalse($route->matches(Path::of('/foo/bar/baz')));

        $route = Route::of(
            Action::get(),
            new Name('foo.bar.baz'),
            $this->definition
        );

        $this->assertTrue($route->matches(Path::of('/foo/bar/baz/some-uuid')));
        $this->assertFalse($route->matches(Path::of('/foo/bar/baz/')));
    }

    public function testIdentity()
    {
        $route = Route::of(
            Action::get(),
            new Name('foo'),
            $this->definition
        );

        $identity = $route->identity(Path::of('/foo/42'));

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertSame('42', $identity->toString());
    }

    public function testIdentityWhenNotInPath()
    {
        $route = Route::of(
            Action::get(),
            new Name('foo'),
            $this->definition
        );

        $identity = $route->identity(Path::of('/foo/'));

        $this->assertNull($identity);
    }

    public function cases(): array
    {
        return [
            [Action::list(), '{+prefix}/foo/bar/baz/'],
            [Action::get(), '{+prefix}/foo/bar/baz/{identity}'],
            [Action::create(), '{+prefix}/foo/bar/baz/'],
            [Action::update(), '{+prefix}/foo/bar/baz/{identity}'],
            [Action::remove(), '{+prefix}/foo/bar/baz/{identity}'],
            [Action::link(), '{+prefix}/foo/bar/baz/{identity}'],
            [Action::unlink(), '{+prefix}/foo/bar/baz/{identity}'],
            [Action::options(), '{+prefix}/foo/bar/baz/'],
        ];
    }
}
