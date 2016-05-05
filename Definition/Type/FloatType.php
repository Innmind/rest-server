<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\TypeInterface,
    Exception\DenormalizationException,
    Exception\NormalizationException
};
use Innmind\Immutable\{
    CollectionInterface,
    SetInterface,
    Set
};

final class FloatType implements TypeInterface
{
    private static $identifiers;

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(CollectionInterface $config): TypeInterface
    {
        return new self;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        try {
            return (float) $data;
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be a float');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        try {
            return (float) $data;
        } catch (\Throwable $e) {
            throw new NormalizationException('The value must be a float');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function identifiers(): SetInterface
    {
        if (self::$identifiers === null) {
            self::$identifiers = (new Set('string'))->add('float');
        }

        return self::$identifiers;
    }
}
