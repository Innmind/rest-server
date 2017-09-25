<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResourceInterface,
    IdentityInterface
};
use Innmind\Http\{
    Message\ServerRequest,
    Header\Location,
    Header\LocationValue,
    Header
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
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): MapInterface {
        $map = new Map('string', Header::class);

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
