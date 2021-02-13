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
    /**
     * {@inheritdoc}
     */
    public function and(Specification $specification): CompositeInterface
    {
        return new AndFilter($this, $specification);
    }

    /**
     * {@inheritdoc}
     */
    public function or(Specification $specification): CompositeInterface
    {
        return new OrFilter($this, $specification);
    }

    /**
     * {@inheritdoc}
     */
    public function not(): Not
    {
        return new NotFilter($this);
    }
}
