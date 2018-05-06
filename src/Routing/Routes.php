<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Action,
    Definition\HttpResource,
    Definition\Directory,
};
use Innmind\Immutable\{
    Set,
    SequenceInterface,
    Sequence,
    MapInterface,
    Map,
};

final class Routes implements \Iterator
{
    private $routes;

    public function __construct(Route ...$routes)
    {
        $this->routes = Set::of(Route::class, ...$routes);
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

                    return in_array(
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
    public static function from(MapInterface $directories): self
    {
        return $directories
            ->reduce(
                new Map('string', HttpResource::class),
                static function(MapInterface $definitions, string $name, Directory $directory): MapInterface {
                    return $definitions->merge($directory->flatten());
                }
            )
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
        $self = clone $this;
        $self->routes = $self->routes->merge($routes->routes);

        return $self;
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
