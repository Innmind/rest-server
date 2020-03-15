<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type,
    Exception\DenormalizationException,
    Exception\NormalizationException,
};
use Innmind\TimeContinuum\{
    Format,
    Earth\Format\ISO8601,
    Clock,
    PointInTime,
};

final class PointInTimeType implements Type
{
    private Clock $clock;
    private Format $format;

    public function __construct(
        Clock $clock,
        Format $format = null
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
        if (!$data instanceof PointInTime) {
            throw new NormalizationException('The value must be a point in time');
        }

        return $data->format($this->format);
    }

    public function toString(): string
    {
        return 'date<'.$this->format->toString().'>';
    }
}
