<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResourceInterface;
use Innmind\Specification\{
    NotInterface,
    SpecificationInterface
};

final class NotFilter implements NotInterface
{
    use Composite;

    private $specification;

    public function __construct(SpecificationInterface $specification)
    {
        $this->specification = $specification;
    }

    /**
     * {@inheritdoc}
     */
    public function specification(): SpecificationInterface
    {
        return $this->specification;
    }

    public function isSatisfiedBy(HttpResourceInterface $resource): bool
    {
        return !$this->specification->isSatisfiedBy($resource);
    }
}
