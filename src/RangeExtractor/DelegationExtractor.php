<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFound,
    Exception\InvalidArgumentException,
    Request\Range
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\SetInterface;

final class DelegationExtractor implements Extractor
{
    private $extractors;

    public function __construct(SetInterface $extractors)
    {
        if ((string) $extractors->type() !== Extractor::class) {
            throw new InvalidArgumentException;
        }

        $this->extractors = $extractors;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(ServerRequest $request): Range
    {
        $range = $this
            ->extractors
            ->reduce(
                null,
                function($carry, Extractor $extractor) use ($request) {
                    if ($carry instanceof Range) {
                        return $carry;
                    }

                    try {
                        return $extractor->extract($request);
                    } catch (RangeNotFound $e) {
                        //pass
                    }
                }
            );

        if ($range instanceof Range) {
            return $range;
        }

        throw new RangeNotFound;
    }
}
