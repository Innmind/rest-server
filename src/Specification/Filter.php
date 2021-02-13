<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\HttpResource;
use Innmind\Specification\{
    Comparator,
    Sign,
};

/**
 * @psalm-immutable
 */
final class Filter implements Comparator
{
    use Composite;

    private string $property;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
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
    public function sign(): Sign
    {
        return Sign::equality();
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
        /** @psalm-suppress ImpureMethodCall */
        return $resource->property($this->property)->value() === $this->value;
    }
}
