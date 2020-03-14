<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResource;
use Innmind\Specification\{
    Not,
    Specification,
};

final class NotFilter implements Not
{
    use Composite;

    private Specification $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    /**
     * {@inheritdoc}
     */
    public function specification(): Specification
    {
        return $this->specification;
    }

    public function isSatisfiedBy(HttpResource $resource): bool
    {
        return !$this->specification->isSatisfiedBy($resource);
    }
}
