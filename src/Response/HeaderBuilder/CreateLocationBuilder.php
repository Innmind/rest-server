<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResource as HttpResourceInterface,
    Identity,
    Action,
    Router,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header\Location,
    Header\LocationValue,
    Header,
};
use Innmind\Immutable\Set;

final class CreateLocationBuilder implements CreateBuilder
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        Identity $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): Set {
        /** @var Set<Header> */
        return Set::of(
            Header::class,
            new Location(
                new LocationValue(
                    $this->router->generate(
                        Action::get(),
                        $definition,
                        $identity
                    )
                )
            )
        );
    }
}
