<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    Identity,
    Router,
    Action,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
    Header\Value,
    Header\Link,
    Header\LinkValue,
};
use Innmind\Specification\Specification;
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;

final class ListLinksBuilder implements ListBuilder
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function __invoke(
        Set $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): Set {
        /** @var Set<Header<Header\Value>> */
        $headers = Set::of(Header::class);

        if ($identities->size() === 0) {
            return $headers;
        }

        /**
         * @psalm-suppress MixedArgument
         * @psalm-suppress InvalidArgument
         */
        return $headers->add(
            new Link(
                ...unwrap($identities->reduce(
                    Set::of(Value::class),
                    function(Set $carry, Identity $identity) use ($definition): Set {
                        return $carry->add(new LinkValue(
                            $this->router->generate(
                                Action::get(),
                                $definition,
                                $identity
                            ),
                            'resource'
                        ));
                    }
                )),
            )
        );
    }
}
