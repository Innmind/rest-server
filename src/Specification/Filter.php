<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResource;
use Innmind\Specification\ComparatorInterface;

final class Filter implements ComparatorInterface
{
    use Composite;

    private $property;
    private $value;

    public function __construct(string $property, $value)
    {
        $this->property = $property;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function property(): string
    {
        return $this->property;
    }

    /**
     * {@inheritdoc}
     */
    public function sign(): string
    {
        return '==';
    }

    /**
     * {@inheritdoc}
     */
    public function value()
    {
        return $this->value;
    }

    public function isSatisfiedBy(HttpResource $resource): bool
    {
        return $resource->property($this->property)->value() === $this->value;
    }
}
