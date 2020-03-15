<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFound,
    Request\Range,
};
use Innmind\Http\Message\ServerRequest;

final class QueryExtractor implements Extractor
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequest $request): Range
    {
        if (
            !$request->query()->contains('range') ||
            !\is_array($request->query()->get('range')->value()) ||
            \count($request->query()->get('range')->value()) !== 2
        ) {
            throw new RangeNotFound;
        }

        return new Range(
            $request
                ->query()
                ->get('range')
                ->value()[0],
            $request
                ->query()
                ->get('range')
                ->value()[1]
        );
    }
}
