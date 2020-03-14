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
    PathInterface,
    UrlInterface,
};
use Innmind\Immutable\Map;

final class Router
{
    private Routes $routes;
    private Prefix $prefix;
    private Map $variables;

    public function __construct(Routes $routes, Prefix $prefix = null)
    {
        $this->routes = $routes;
        $this->prefix = $prefix ?? Prefix::none();
        $this->variables = new Map('string', 'variable');

        if ($prefix instanceof Prefix) {
            $this->variables = $this->variables->put('prefix', (string) $prefix);
        }
    }

    public function match(PathInterface $path): Match
    {
        try {
            $path = $this->prefix->outOf($path);
        } catch (LogicException $e) {
            throw new RouteNotFound((string) $path, 0, $e);
        }

        return $this->routes->match($path);
    }

    public function generate(
        Action $action,
        HttpResource $definition,
        Identity $identity = null
    ): UrlInterface {
        $route = $this->routes->get($action, $definition);

        return $route
            ->template()
            ->expand(
                $this->variables->put('identity', (string) $identity)
            );
    }
}
