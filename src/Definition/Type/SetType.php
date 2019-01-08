<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Definition\Types,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set,
};

final class SetType implements Type
{
    private static $identifiers;
    private $inner;
    private $innerKey;

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(MapInterface $config, Types $types): Type
    {
        if (
            (string) $config->keyType() !== 'scalar' ||
            (string) $config->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 1 must be of type MapInterface<scalar, variable>');
        }

        $type = new self;
        $type->innerKey = $config->get('inner');
        $type->inner = $types->build(
            $config->get('inner'),
            $config->remove('inner')
        );

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        if (!\is_array($data)) {
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
        return self::$identifiers ?? self::$identifiers = Set::of('string', 'set');
    }

    public function __toString(): string
    {
        return \sprintf('set<%s>', $this->inner);
    }
}
