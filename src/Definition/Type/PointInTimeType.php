<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};
use Innmind\TimeContinuum\{
    FormatInterface,
    Format\ISO8601,
    TimeContinuumInterface,
    PointInTimeInterface,
};

final class PointInTimeType implements Type
{
    private $clock;
    private $format;

    public function __construct(
        TimeContinuumInterface $clock,
        FormatInterface $format = null
    ) {
        $this->clock = $clock;
        $this->format = $format ?? new ISO8601;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        try {
            return $this->clock->at($data, $this->format);
        } catch (\Throwable $e) {
            throw new DenormalizationException('The value must be a point in time');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        if (!$data instanceof PointInTimeInterface) {
            throw new NormalizationException('The value must be a point in time');
        }

        return $data->format($this->format);
    }

    public function __toString(): string
    {
        return 'date<'.$this->format.'>';
    }
}
