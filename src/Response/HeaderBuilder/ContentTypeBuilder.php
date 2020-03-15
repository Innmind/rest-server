<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\Formats;
use Innmind\Http\{
    Message\ServerRequest,
    Header,
    Header\Value,
    Header\ContentType,
    Header\ContentTypeValue,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\join;

trait ContentTypeBuilder
{
    /**
     * @return Set<Header>
     */
    private function buildHeaderFrom(
        Formats $formats,
        ServerRequest $request
    ): Set {
        $format = $formats->matching(
            join(
                ', ',
                $request
                    ->headers()
                    ->get('Accept')
                    ->values()
                    ->mapTo(
                        'string',
                        static fn(Value $value): string => $value->toString(),
                    ),
            )->toString(),
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
