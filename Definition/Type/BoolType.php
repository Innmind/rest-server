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

final class BoolType implements TypeInterface
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

    /**
     * {@inheritdoc}
     */
    public static function identifiers(): SetInterface
    {
        if (self::$identifiers === null) {
            self::$identifiers = (new Set('string'))
                ->add('bool')
                ->add('boolean');
        }

        return self::$identifiers;
    }
}
