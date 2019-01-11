<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};
use Innmind\Immutable\{
    Map,
    MapInterface,
};

final class MapType implements Type
{
    private $key;
    private $inner;
    private $innerKey;
    private $innerValue;

    public function __construct(
        string $key,
        string $value,
        Type $keyType,
        Type $valueType
    ) {
        $this->innerKey = $key;
        $this->innerValue = $value;
        $this->key = $keyType;
        $this->inner = $valueType;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        if (!\is_array($data)) {
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

    public function __toString(): string
    {
        return \sprintf(
            'map<%s, %s>',
            $this->key,
            $this->inner
        );
    }
}
