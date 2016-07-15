<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResourceInterface,
    IdentityInterface
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\Location,
    Header\LocationValue,
    Header\HeaderInterface
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    MapInterface,
    Map
};

final class CreateLocationBuilder implements CreateBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(
        IdentityInterface $identity,
        ServerRequestInterface $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): MapInterface {
        $map = new Map('string', HeaderInterface::class);

        return $map->put(
            'Location',
            new Location(
                new LocationValue(
                    Url::fromString(
                        rtrim((string) $request->url()->path(), '/').'/'.$identity
                    )
                )
            )
        );
    }
}
