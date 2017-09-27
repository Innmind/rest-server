<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFound,
    Request\Range
};
use Innmind\Http\Message\ServerRequest;

final class DelegationExtractor implements Extractor
{
    private $extractors;

    public function __construct(Extractor ...$extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(ServerRequest $request): Range
    {
        foreach ($this->extractors as $extractor) {
            try {
                return $extractor->extract($request);
            } catch (RangeNotFound $e) {
                //pass
            }
        }

        throw new RangeNotFound;
    }
}
