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

final class DateType implements Type
{
    private static $identifiers;
    private $format;

    public function __construct(string $format = \DateTime::ISO8601)
    {
        $this->format = $format;
    }

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
        if (\is_string($data)) {
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
        return self::$identifiers ?? self::$identifiers = Set::of('string', 'date');
    }

    public function __toString(): string
    {
        return 'date<'.$this->format.'>';
    }
}
