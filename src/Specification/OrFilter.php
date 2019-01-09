<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResource;
use Innmind\Specification\{
    Specification,
    Composite as CompositeInterface,
    Operator,
};

final class OrFilter implements CompositeInterface
{
    use Composite;

    private $left;
    private $right;
    private $operator;

    public function __construct(
        Specification $left,
        Specification $right
    ) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = Operator::or();
    }

    /**
     * {@inheritdoc}
     */
    public function left(): Specification
    {
        return $this->left;
    }

    /**
     * {@inheritdoc}
     */
    public function right(): Specification
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

    public function isSatisfiedBy(HttpResource $resource): bool
    {
        return $this->left->isSatisfiedBy($resource) || $this->right->isSatisfiedBy($resource);
    }
}
