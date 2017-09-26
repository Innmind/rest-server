<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    Identity
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
    Header\Value,
    Header\Link,
    Header\LinkValue
};
use Innmind\Specification\SpecificationInterface;
use Innmind\Url\Url;
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map,
    Set
};

final class ListLinksBuilder implements ListBuilder
{
    /**
     * {@inheritdoc}
     */
    public function build(
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

        $path = $request->url()->path();

        return $map->put(
            'Link',
            new Link(
                ...$identities->reduce(
                    new Set(Value::class),
                    function(Set $carry, Identity $identity) use ($path): Set {
                        return $carry->add(new LinkValue(
                            Url::fromString(
                                rtrim((string) $path, '/').'/'.$identity
                            ),
                            'resource'
                        ));
                    }
                )
            )
        );
    }
}
