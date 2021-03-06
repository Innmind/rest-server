<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};

final class IntType implements Type
{
    public function denormalize($data)
    {
        try {
            return (int) $data;
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be an integer');
        }
    }

    public function normalize($data)
    {
        try {
            return (int) $data;
        } catch (\Throwable $e) {
            throw new NormalizationException('The value must be an integer');
        }
    }

    public function toString(): string
    {
        return 'int';
    }
}
