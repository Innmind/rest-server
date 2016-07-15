<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\TypeInterface,
    Definition\Types,
    Exception\DenormalizationException,
    Exception\NormalizationException
};
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set
};

final class IntType implements TypeInterface
{
    private static $identifiers;

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(MapInterface $config, Types $types): TypeInterface
    {
        return new self;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        try {
            return (int) $data;
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be an integer');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        try {
            return (int) $data;
        } catch (\Throwable $e) {
            throw new NormalizationException('The value must be an integer');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function identifiers(): SetInterface
    {
        if (self::$identifiers === null) {
            self::$identifiers = (new Set('string'))
                ->add('int')
                ->add('integer');
        }

        return self::$identifiers;
    }
}
