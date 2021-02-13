<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Specification\{
    Specification,
    Composite as CompositeInterface,
    Not,
};

/**
 * @psalm-immutable
 */
trait Composite
{
    public function and(Specification $specification): CompositeInterface
    {
        return new AndFilter($this, $specification);
    }

    public function or(Specification $specification): CompositeInterface
    {
        return new OrFilter($this, $specification);
    }

    public function not(): Not
    {
        return new NotFilter($this);
    }
}
