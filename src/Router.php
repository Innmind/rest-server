<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Routing\Routes,
    Routing\Prefix,
    Routing\Match,
    Definition\HttpResource,
    Exception\LogicException,
    Exception\RouteNotFound,
};
use Innmind\Url\{
    Path,
    Url,
};
use Innmind\Immutable\Map;

final class Router
{
    private Routes $routes;
    private Prefix $prefix;
    /** @var Map<string, scalar|array> */
    private Map $variables;

    public function __construct(Routes $routes, Prefix $prefix = null)
    {
        $this->routes = $routes;
        $this->prefix = $prefix ?? Prefix::none();
        /** @var Map<string, scalar|array> */
        $this->variables = Map::of('string', 'scalar|array');

        if ($prefix instanceof Prefix) {
            $this->variables = $this->variables->put('prefix', $prefix->toString());
        }
    }

    public function match(Path $path): Match
    {
        try {
            $path = $this->prefix->outOf($path);
        } catch (LogicException $e) {
            throw new RouteNotFound($path->toString(), 0, $e);
        }

        return $this->routes->match($path);
    }

    public function generate(
        Action $action,
        HttpResource $definition,
        Identity $identity = null
    ): Url {
        $route = $this->routes->get($action, $definition);

        return $route
            ->template()
            ->expand(
                $this->variables->put('identity', $identity ? $identity->toString() : '')
            );
    }
}
