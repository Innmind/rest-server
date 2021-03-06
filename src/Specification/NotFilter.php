<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResource;
use Innmind\Specification\{
    Not,
    Specification,
};

/**
 * @psalm-immutable
 */
final class NotFilter implements Not
{
    use Composite;

    private Specification $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    public function specification(): Specification
    {
        return $this->specification;
    }

    public function isSatisfiedBy(HttpResource $resource): bool
    {
        /** @psalm-suppress UndefinedInterfaceMethod */
        return !$this->specification->isSatisfiedBy($resource);
    }
}
