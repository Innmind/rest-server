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

class ArrayType implements TypeInterface
{
    private static $identifiers;
    private $inner;
    private $innerKey;
    private $useSet = true;

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(CollectionInterface $config): TypeInterface
    {
        $type = new self;
        $type->innerKey = $config->get('inner');
        $type->inner = $config
            ->get('_types')
            ->build(
                $config->get('inner'),
                $config
                    ->unset('_types')
                    ->unset('inner')
            );

        if ($config->hasKey('use_set')) {
            $type->useSet = (bool) $config->get('use_set');
        }

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

        $denormalized = [];

        foreach ($data as $value) {
            $denormalized[] = $this->inner->denormalize($value);
        }

        if ($this->useSet) {
            $set = new Set($this->innerKey);

            foreach ($denormalized as $value) {
                $set = $set->add($value);
            }

            return $set;
        }

        return $denormalized;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        if (!is_array($data) && !$data instanceof \Traversable) {
            throw new NormalizationException('The value must be traversable');
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
            self::$identifiers = (new Set('string'))->add('array');
        }

        return self::$identifiers;
    }
}
