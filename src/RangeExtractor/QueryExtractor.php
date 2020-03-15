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
        if (!$request->query()->contains('range')) {
            throw new RangeNotFound;
        }

        $range = $request->query()->get('range')->value();

        if (!\is_array($range)) {
            throw new RangeNotFound;
        }

        if (\count($range) !== 2) {
            throw new RangeNotFound;
        }

        /** @psalm-suppress MixedArgument */
        return new Range(
            $range[0],
            $range[1],
        );
    }
}
