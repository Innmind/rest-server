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
    SetInterface,
    Set,
};

trait ContentTypeBuilder
{
    /**
     * @param Formats $formats
     * @param ServerRequest $request
     *
     * @return SetInterface<Header>
     */
    private function buildHeaderFrom(
        Formats $formats,
        ServerRequest $request
    ): SetInterface {
        $format = $formats->matching(
            (string) $request
                ->headers()
                ->get('Accept')
                ->values()
                ->join(', ')
        );

        return Set::of(
            Header::class,
            new ContentType(
                new ContentTypeValue(
                    $format->preferredMediaType()->topLevel(),
                    $format->preferredMediaType()->subType()
                )
            )
        );
    }
}
