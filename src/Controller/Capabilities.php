<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Router,
    Routing\Routes,
    Routing\Route,
    Action,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
    Header\Link,
    Header\LinkValue,
};
use Innmind\Immutable\Sequence;

final class Capabilities
{
    private $routes;
    private $router;

    public function __construct(Routes $routes, Router $router)
    {
        $this->routes = Sequence::of(...$routes)->filter(static function(Route $route): bool {
            return $route->action() === Action::options();
        });
        $this->router = $router;
    }

    public function __invoke(ServerRequest $request): Response
    {
        return new Response\Response(
            new StatusCode($code = StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code)),
            $request->protocolVersion(),
            Headers::of(
                new Link(
                    ...$this->routes->map(function(Route $route): LinkValue {
                        return new LinkValue(
                            $this->router->generate($route->action(), $route->definition()),
                            (string) $route->name()
                        );
                    })
                )
            )
        );
    }
}
