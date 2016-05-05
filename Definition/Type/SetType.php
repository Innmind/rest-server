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

final class SetType implements TypeInterface
{
    private static $identifiers;
    private $inner;
    private $innerKey;

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
