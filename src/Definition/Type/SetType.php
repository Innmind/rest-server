<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;

final class SetType implements Type
{
    private Type $inner;
    private string $type;

    public function __construct(string $value, Type $type)
    {
        $this->type = $value;
        $this->inner = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        if (!\is_array($data)) {
            throw new DenormalizationException(sprintf(
                'The value must be an array of %s',
                $this->type
            ));
        }

        $set = Set::of($this->type);

        foreach ($data as $value) {
            $set = $set->add($this->inner->denormalize($value));
        }

        return $set;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        if (!$data instanceof Set) {
            throw new NormalizationException('The value must be a set');
        }

        $normalized = [];

        foreach (unwrap($data) as $value) {
            $normalized[] = $this->inner->normalize($value);
        }

        return $normalized;
    }

    public function toString(): string
    {
        return \sprintf('set<%s>', $this->inner->toString());
    }
}
