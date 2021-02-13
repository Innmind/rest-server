<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Action,
    Definition\HttpResource,
    Definition\Directory,
    Exception\RouteNotFound,
};
use Innmind\Url\Path;
use Innmind\Immutable\{
    Set,
    Sequence,
    Map,
};
use function Innmind\Immutable\{
    unwrap,
    first,
};

/**
 * @implements \Iterator<Route>
 */
final class Routes implements \Iterator
{
    /** @var Set<Route> */
    private Set $routes;
    /** @var list<Route> */
    private array $array;
    /** @var Map<HttpResource, Map<Action, Set<Route>>> */
    private Map $definitions;

    public function __construct(Route ...$routes)
    {
        $this->routes = Set::of(Route::class, ...$routes);
        $this->array = $routes;
        /** @var Map<HttpResource, Map<Action, Set<Route>>> */
        $this->definitions = Map::of(HttpResource::class, Map::class);

        if ($this->routes->size() === 0) {
            return;
        }

        /** @var Map<HttpResource, Map<Action, Set<Route>>> */
        $this->definitions = $this
            ->routes
            ->groupBy(static function(Route $route): HttpResource {
                return $route->definition();
            })
            ->reduce(
                $this->definitions,
                static function(Map $definitions, HttpResource $definition, Set $routes): Map {
                    return $definitions->put(
                        $definition,
                        $routes->groupBy(static function(Route $route): Action {
                            return $route->action();
                        })
                    );
                }
            );
    }

    public static function of(Name $name, HttpResource $definition): self
    {
        /** @psalm-suppress MixedArgument */
        return new self(
            ...unwrap(Action::all()
                ->filter(static function(Action $action) use ($definition): bool {
                    return $definition->allow($action);
                })
                ->reduce(
                    Sequence::of(Route::class),
                    static function(Sequence $routes, Action $action) use ($name, $definition): Sequence {
                        return $routes->add(
                            Route::of($action, $name, $definition)
                        );
                    }
                ))
        );
    }

    /**
     * @param Map<string, Directory> $directories
     */
    public static function from(Directory $directory): self
    {
        return $directory
            ->flatten()
            ->reduce(
                new self,
                static function(self $routes, string $name, HttpResource $definition): self {
                    return $routes->merge(self::of(
                        new Name($name),
                        $definition
                    ));
                }
            );
    }

    public function merge(self $routes): self
    {
        return new self(...$this, ...$routes);
    }

    public function match(Path $path): Match
    {
        $match = $this->routes->reduce(
            null,
            static function(?Match $match, Route $route) use ($path): ?Match {
                if ($match instanceof Match) {
                    return $match;
                }

                if ($route->matches($path)) {
                    return new Match(
                        $route->definition(),
                        $route->identity($path)
                    );
                }

                return null;
            }
        );

        if ($match instanceof Match) {
            return $match;
        }

        throw new RouteNotFound($path->toString());
    }

    public function get(Action $action, HttpResource $definition): Route
    {
        return first($this->definitions->get($definition)->get($action));
    }

    public function current(): Route
    {
        return \current($this->array);
    }

    public function key(): int
    {
        return \key($this->array);
    }

    public function next(): void
    {
        \next($this->array);
    }

    public function rewind(): void
    {
        \reset($this->array);
    }

    public function valid(): bool
    {
        return \current($this->array) instanceof Route;
    }
}
