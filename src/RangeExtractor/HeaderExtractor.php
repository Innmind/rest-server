<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFound,
    Request\Range,
};
use Innmind\Http\Message\ServerRequest;
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

        return new Range(
            first($request->headers()->get('Range')->values())->firstPosition(),
            first($request->headers()->get('Range')->values())->lastPosition(),
        );
    }
}
