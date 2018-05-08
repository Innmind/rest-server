<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Specification\{
    SpecificationInterface,
    CompositeInterface,
    NotInterface,
};

trait COmposite
{
    /**
     * {@inheritdoc}
     */
    public function and(SpecificationInterface $specification): CompositeInterface
    {
        return new AndFilter($this, $specification);
    }

    /**
     * {@inheritdoc}
     */
    public function or(SpecificationInterface $specification): CompositeInterface
    {
        return new OrFilter($this, $specification);
    }

    /**
     * {@inheritdoc}
     */
    public function not(): NotInterface
    {
        return new NotFilter($this);
    }
}
