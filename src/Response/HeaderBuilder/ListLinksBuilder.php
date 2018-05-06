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
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map,
    Set,
};

final class ListLinksBuilder implements ListBuilder
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        SetInterface $identities,
        ServerRequest $request,
        HttpResource $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): MapInterface {
        $map = new Map('string', Header::class);

        if ($identities->size() === 0) {
            return $map;
        }

        return $map->put(
            'Link',
            new Link(
                ...$identities->reduce(
                    new Set(Value::class),
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
                )
            )
        );
    }
}
