<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};

final class StringType implements Type
{
    public function denormalize($data)
    {
        try {
            return (string) $data;
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be a string');
        }
    }

    public function normalize($data)
    {
        try {
            return (string) $data;
        } catch (\Throwable $e) {
            throw new NormalizationException('The value must be a string');
        }
    }

    public function toString(): string
    {
        return 'string';
    }
}
