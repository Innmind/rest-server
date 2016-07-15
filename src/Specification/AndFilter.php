<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResourceInterface;
use Innmind\Specification\{
    SpecificationInterface,
    CompositeInterface,
    Operator
};

final class AndFilter implements CompositeInterface
{
    use Composite;

    private $left;
    private $right;
    private $operator;

    public function __construct(
        SpecificationInterface $left,
        SpecificationInterface $right
    ) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = new Operator(Operator::AND);
    }

    /**
     * {@inheritdoc}
     */
    public function left(): SpecificationInterface
    {
        return $this->left;
    }

    /**
     * {@inheritdoc}
     */
    public function right(): SpecificationInterface
    {
        return $this->right;
    }

    /**
     * {@inheritdoc}
     */
    public function operator(): Operator
    {
        return $this->operator;
    }

    public function isSatisfiedBy(HttpResourceInterface $resource): bool
    {
        return $this->left->isSatisfiedBy($resource) && $this->right->isSatisfiedBy($resource);
    }
}
