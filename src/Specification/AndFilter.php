<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResource;
use Innmind\Specification\{
    Specification,
    Composite as CompositeInterface,
    Operator,
};

/**
 * @psalm-immutable
 */
final class AndFilter implements CompositeInterface
{
    use Composite;

    private Specification $left;
    private Specification $right;
    private Operator $operator;

    public function __construct(
        Specification $left,
        Specification $right
    ) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = Operator::and();
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
        /** @psalm-suppress UndefinedInterfaceMethod */
        return $this->left->isSatisfiedBy($resource) && $this->right->isSatisfiedBy($resource);
    }
}
