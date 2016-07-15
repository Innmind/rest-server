<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\TypeInterface,
    Definition\Types,
    Exception\DenormalizationException,
    Exception\NormalizationException,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set
};

final class SetType implements TypeInterface
{
    private static $identifiers;
    private $inner;
    private $innerKey;

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(MapInterface $config, Types $types): TypeInterface
    {
        if (
            (string) $config->keyType() !== 'scalar' ||
            (string) $config->valueType() !== 'variable'
        ) {
            throw new InvalidArgumentException;
        }

        $type = new self;
        $type->innerKey = $config->get('inner');
        $type->inner = $types->build(
            $config->get('inner'),
            $config->remove('inner'),
            $types
        );

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        if (!is_array($data)) {
            throw new DenormalizationException(sprintf(
                'The value must be an array of %s',
                $this->innerKey
            ));
        }

        $set = new Set($this->innerKey);

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
        if (!$data instanceof SetInterface) {
            throw new NormalizationException('The value must be a set');
        }

        $normalized = [];

        foreach ($data as $value) {
            $normalized[] = $this->inner->normalize($value);
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public static function identifiers(): SetInterface
    {
        if (self::$identifiers === null) {
            self::$identifiers = (new Set('string'))->add('set');
        }

        return self::$identifiers;
    }
}
