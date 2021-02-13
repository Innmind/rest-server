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

    public function denormalize($data)
    {
        try {
            /** @psalm-suppress MixedArgument */
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

    public function normalize($data)
    {
        if (\is_string($data)) {
            try {
                /** @var \DateTimeImmutable */
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

    public function toString(): string
    {
        return 'date<'.$this->format.'>';
    }
}
