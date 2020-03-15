<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFound,
    Request\Range,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header\Range as RangeHeader,
};
use function Innmind\Immutable\first;

final class HeaderExtractor implements Extractor
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequest $request): Range
    {
        if (!$request->headers()->contains('Range')) {
            throw new RangeNotFound;
        }

        $range = $request->headers()->get('Range');

        if (!$range instanceof RangeHeader) {
            throw new RangeNotFound;
        }

        return new Range(
            first($range->values())->firstPosition(),
            first($range->values())->lastPosition(),
        );
    }
}
