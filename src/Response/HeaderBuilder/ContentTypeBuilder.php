<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\Formats;
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\ParameterInterface
};
use Innmind\Immutable\{
    MapInterface,
    Map
};

trait ContentTypeBuilder
{
    /**
     * @param Formats $formats
     * @param ServerRequestInterface $request
     *
     * @return MapInterface<string, HeaderInterface>
     */
    private function buildHeaderFrom(
        Formats $formats,
        ServerRequestInterface $request
    ): MapInterface {
        $map = new Map('string', HeaderInterface::class);
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
                    $format->preferredMediaType()->subType(),
                    new Map('string', ParameterInterface::class)
                )
            )
        );
    }
}
