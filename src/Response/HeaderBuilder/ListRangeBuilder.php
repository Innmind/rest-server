<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
    Header\AcceptRanges,
    Header\AcceptRangesValue,
    Header\ContentRange,
    Header\ContentRangeValue,
};
use Innmind\Specification\Specification;
use Innmind\Immutable\Set;

final class ListRangeBuilder implements ListBuilder
{
    public function __invoke(
        Set $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): Set {
        /** @var Set<Header<Header\Value>> */
        $headers = Set::of(Header::class);

        if (!$definition->isRangeable()) {
            return $headers;
        }

        /** @psalm-suppress InvalidArgument */
        $headers = $headers->add(
            new AcceptRanges(new AcceptRangesValue('resources'))
        );

        if (!$range instanceof Range) {
            return $headers;
        }

        $length = $range->lastPosition() - $range->firstPosition();

        /** @psalm-suppress InvalidArgument */
        return $headers->add(
            new ContentRange(
                new ContentRangeValue(
                    'resources',
                    $range->firstPosition(),
                    $last = $range->firstPosition() + $identities->size(),
                    $identities->size() < $length ? $last : $last + $length
                )
            )
        );
    }
}
