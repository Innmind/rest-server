<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFoundException,
    Request\Range
};
use Innmind\Http\Message\ServerRequest;

final class HeaderExtractor implements Extractor
{
    /**
     * {@inheritdoc}
     */
    public function extract(ServerRequest $request): Range
    {
        if (!$request->headers()->has('Range')) {
            throw new RangeNotFoundException;
        }

        return new Range(
            $request
                ->headers()
                ->get('Range')
                ->values()
                ->current()
                ->firstPosition(),
            $request
                ->headers()
                ->get('Range')
                ->values()
                ->current()
                ->lastPosition()
        );
    }
}
