<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    IdentityInterface
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface,
    Header\HeaderValueInterface,
    Header\Link,
    Header\LinkValue,
    Header\ParameterInterface
};
use Innmind\Specification\SpecificationInterface;
use Innmind\Url\Url;
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map,
    Set
};

final class ListLinksBuilder implements ListBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(
        SetInterface $identities,
        ServerRequestInterface $request,
        HttpResource $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): MapInterface {
        $map = new Map('string', HeaderInterface::class);
        $path = $request->url()->path();

        return $map->put(
            'Link',
            new Link(
                $identities->reduce(
                    new Set(HeaderValueInterface::class),
                    function(Set $carry, IdentityInterface $identity) use ($path): Set {
                        return $carry->add(new LinkValue(
                            Url::fromString(
                                rtrim((string) $path, '/').'/'.$identity
                            ),
                            'resource',
                            new Map('string', ParameterInterface::class)
                        ));
                    }
                )
            )
        );
    }
}
