<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};

final class FloatType implements Type
{
    public function denormalize($data)
    {
        try {
            return (float) $data;
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be a float');
        }
    }

    public function normalize($data)
    {
        try {
            return (float) $data;
        } catch (\Throwable $e) {
            throw new NormalizationException('The value must be a float');
        }
    }

    public function toString(): string
    {
        return 'float';
    }
}
