<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResource as HttpResourceInterface,
    Identity
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

final class CreateLocationBuilder implements CreateBuilder
{
    /**
     * {@inheritdoc}
     */
    public function build(
        Identity $identity,
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
