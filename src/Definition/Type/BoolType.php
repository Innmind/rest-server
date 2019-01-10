<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};

final class BoolType implements Type
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        try {
            return (bool) $data;
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be a boolean');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        try {
            return (bool) $data;
        } catch (\Throwable $e) {
            throw new NormalizationException('The value must be a boolean');
        }
    }

    public function __toString(): string
    {
        return 'bool';
    }
}
