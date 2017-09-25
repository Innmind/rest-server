<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFoundException,
    Exception\InvalidArgumentException,
    Request\Range
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\SetInterface;

final class DelegationExtractor implements ExtractorInterface
{
    private $extractors;

    public function __construct(SetInterface $extractors)
    {
        if ((string) $extractors->type() !== ExtractorInterface::class) {
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
                function($carry, ExtractorInterface $extractor) use ($request) {
                    if ($carry instanceof Range) {
                        return $carry;
                    }

                    try {
                        return $extractor->extract($request);
                    } catch (RangeNotFoundException $e) {
                        //pass
                    }
                }
            );

        if ($range instanceof Range) {
            return $range;
        }

        throw new RangeNotFoundException;
    }
}
