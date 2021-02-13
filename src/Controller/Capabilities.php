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
    Message\StatusCode,
    Headers,
    Header\Link,
    Header\LinkValue,
};
use Innmind\Immutable\Sequence;
use function Innmind\Immutable\unwrap;

final class Capabilities
{
    /** @var Sequence<Route> */
    private Sequence $routes;
    private Router $router;

    public function __construct(Routes $routes, Router $router)
    {
        /** @var Sequence<Route> */
        $this->routes = Sequence::of(Route::class, ...$routes)->filter(static function(Route $route): bool {
            return $route->action() === Action::options();
        });
        $this->router = $router;
    }

    public function __invoke(ServerRequest $request): Response
    {
        /** @psalm-suppress InvalidArgument */
        return new Response\Response(
            $code = StatusCode::of('OK'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                new Link(
                    ...unwrap(
                        $this->routes->mapTo(
                            LinkValue::class,
                            function(Route $route): LinkValue {
                                return new LinkValue(
                                    $this->router->generate($route->action(), $route->definition()),
                                    $route->name()->toString(),
                                );
                            }
                        )
                    ),
                )
            )
        );
    }
}
