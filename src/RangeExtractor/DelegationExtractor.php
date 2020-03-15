<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    Exception\RangeNotFound,
    Request\Range,
};
use Innmind\Http\Message\ServerRequest;

final class DelegationExtractor implements Extractor
{
    /** @var list<Extractor> */
    private array $extractors;

    public function __construct(Extractor ...$extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequest $request): Range
    {
        foreach ($this->extractors as $extract) {
            try {
                return $extract($request);
            } catch (RangeNotFound $e) {
                //pass
            }
        }

        throw new RangeNotFound;
    }
}
