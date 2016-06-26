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
    Set,
    Map,
    MapInterface
};

final class MapType implements TypeInterface
{
    private static $identifiers;
    private $key;
    private $inner;
    private $innerKey;
    private $innerValue;

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(CollectionInterface $config): TypeInterface
    {
        $type = new self;
        $type->innerKey = $config->get('key');
        $type->innerValue = $config->get('inner');
        $type->inner = $config
            ->get('_types')
            ->build(
                $config->get('inner'),
                $config
                    ->unset('_types')
                    ->unset('inner')
                    ->unset('key')
            );
        $type->key = $config
            ->get('_types')
            ->build(
                $config->get('key'),
                $config
                    ->unset('_types')
                    ->unset('inner')
                    ->unset('key')
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
                'The value must be an array of %s mapped to %s',
                $this->innerKey,
                $this->innerValue
            ));
        }

        $map = new Map($this->innerKey, $this->innerValue);

        foreach ($data as $key => $value) {
            $map = $map->put(
                $this->key->denormalize($key),
                $this->inner->denormalize($value)
            );
        }

        return $map;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        if (!$data instanceof MapInterface) {
            throw new NormalizationException('The value must be a map');
        }

        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[$this->key->normalize($key)] = $this->inner->normalize($value);
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public static function identifiers(): SetInterface
    {
        if (self::$identifiers === null) {
            self::$identifiers = (new Set('string'))->add('map');
        }

        return self::$identifiers;
    }
}