<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Action,
    Definition\HttpResource,
    Definition\Directory,
    Exception\RouteNotFound,
};
use Innmind\Url\PathInterface;
use Innmind\Immutable\{
    SetInterface,
    Set,
    SequenceInterface,
    Sequence,
    MapInterface,
    Map,
};

final class Routes implements \Iterator
{
    private $routes;
    private $definitions;

    public function __construct(Route ...$routes)
    {
        $this->routes = Set::of(Route::class, ...$routes);
        $this->definitions = new Map(HttpResource::class, MapInterface::class);

        if ($this->routes->size() === 0) {
            return;
        }

        $this->definitions = $this
            ->routes
            ->groupBy(static function(Route $route): HttpResource {
                return $route->definition();
            })
            ->reduce(
                $this->definitions,
                static function(MapInterface $definitions, HttpResource $definition, SetInterface $routes): MapInterface {
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
        return new self(
            ...Action::all()
                ->filter(static function(Action $action) use ($definition): bool {
                    if (!$definition->options()->contains('actions')) {
                        return true;
                    }

                    if ($action === Action::options()) {
                        return true;
                    }

                    return \in_array(
                        (string) $action,
                        $definition->options()->get('actions'),
                        true
                    );
                })
                ->reduce(
                    new Sequence,
                    static function(SequenceInterface $routes, Action $action) use ($name, $definition): SequenceInterface {
                        return $routes->add(
                            Route::of($action, $name, $definition)
                        );
                    }
                )
        );
    }

    /**
     * @param MapInterface<string, Directory> $directories
     */
    public static function from(Directory $directory): self
    {
        return $directory
            ->flatten()
            ->reduce(
                new self,
                static function(Routes $routes, string $name, HttpResource $definition): Routes {
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

    public function match(PathInterface $path): Match
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

        throw new RouteNotFound((string) $path);
    }

    public function get(Action $action, HttpResource $definition): Route
    {
        return $this
            ->definitions
            ->get($definition)
            ->get($action)
            ->current();
    }

    public function current(): Route
    {
        return $this->routes->current();
    }

    public function key(): int
    {
        return $this->routes->key();
    }

    public function next(): void
    {
        $this->routes->next();
    }

    public function rewind(): void
    {
        $this->routes->rewind();
    }

    public function valid(): bool
    {
        return $this->routes->valid();
    }
}
