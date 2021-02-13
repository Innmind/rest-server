<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};
use Innmind\Immutable\Map;

final class MapType implements Type
{
    private Type $keyType;
    private Type $valueType;
    private string $key;
    private string $value;

    public function __construct(
        string $key,
        string $value,
        Type $keyType,
        Type $valueType
    ) {
        $this->key = $key;
        $this->value = $value;
        $this->keyType = $keyType;
        $this->valueType = $valueType;
    }

    public function denormalize($data)
    {
        if (!\is_array($data)) {
            throw new DenormalizationException(\sprintf(
                'The value must be an array of %s mapped to %s',
                $this->key,
                $this->value
            ));
        }

        $map = Map::of($this->key, $this->value);

        /** @var mixed $value */
        foreach ($data as $key => $value) {
            /** @psalm-suppress MixedArgument */
            $map = $map->put(
                $this->keyType->denormalize($key),
                $this->valueType->denormalize($value)
            );
        }

        return $map;
    }

    public function normalize($data)
    {
        if (!$data instanceof Map) {
            throw new NormalizationException('The value must be a map');
        }

        $normalized = [];

        return $data->reduce(
            [],
            function(array $normalized, $key, $value): array {
                /**
                 * @psalm-suppress MixedArrayOffset
                 * @psalm-suppress MixedAssignment
                 */
                $normalized[$this->keyType->normalize($key)] = $this->valueType->normalize($value);

                return $normalized;
            },
        );
    }

    public function toString(): string
    {
        return \sprintf(
            'map<%s, %s>',
            $this->keyType->toString(),
            $this->valueType->toString(),
        );
    }
}
