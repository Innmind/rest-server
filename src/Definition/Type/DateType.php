<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};

final class DateType implements Type
{
    private string $format;

    public function __construct(string $format = \DateTime::ISO8601)
    {
        $this->format = $format;
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

    public function __toString(): string
    {
        return 'date<'.$this->format.'>';
    }
}
