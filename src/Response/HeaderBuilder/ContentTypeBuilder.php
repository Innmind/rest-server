<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\Formats;
use Innmind\Http\{
    Message\ServerRequest,
    Header,
    Header\ContentType,
    Header\ContentTypeValue,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
};

trait ContentTypeBuilder
{
    /**
     * @param Formats $formats
     * @param ServerRequest $request
     *
     * @return MapInterface<string, Header>
     */
    private function buildHeaderFrom(
        Formats $formats,
        ServerRequest $request
    ): MapInterface {
        $map = new Map('string', Header::class);
        $format = $formats->matching(
            (string) $request
                ->headers()
                ->get('Accept')
                ->values()
                ->join(', ')
        );

        return $map->put(
            'Content-Type',
            new ContentType(
                new ContentTypeValue(
                    $format->preferredMediaType()->topLevel(),
                    $format->preferredMediaType()->subType()
                )
            )
        );
    }
}
