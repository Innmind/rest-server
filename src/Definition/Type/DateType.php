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

final class DateType implements TypeInterface
{
    private static $identifiers;
    private $format = \DateTime::ISO8601;

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

        if ($config->contains('format')) {
            $type->format = (string) $config->get('format');
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        try {
            $data = \DateTimeImmutable::createFromFormat(
                $this->format,
                $data
            );

            if (!$data instanceof \DateTimeInterface) {
                throw new \Exception;
            }

            return $data;
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be a date');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        if (is_string($data)) {
            try {
                $data = $this->denormalize($data);
            } catch (DenormalizationException $e) {
                throw new NormalizationException($e->getMessage());
            }
        }

        if (!$data instanceof \DateTimeInterface) {
            throw new NormalizationException('The value must be a date');
        }

        return $data->format($this->format);
    }

    /**
     * {@inheritdoc}
     */
    public static function identifiers(): SetInterface
    {
        if (self::$identifiers === null) {
            self::$identifiers = (new Set('string'))->add('date');
        }

        return self::$identifiers;
    }
}
