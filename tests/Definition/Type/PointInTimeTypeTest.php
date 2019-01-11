<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type\PointInTimeType,
    Definition\Type,
    Exception\NormalizationException,
    Exception\DenormalizationException,
};
use Innmind\TimeContinuum\{
    TimeContinuum\Earth,
    FormatInterface,
    PointInTimeInterface,
};
use PHPUnit\Framework\TestCase;

class PointInTimeTypeTest extends TestCase
{
    private $clock;
    private $format;

    public function setUp()
    {
        $this->clock = new Earth;
        $this->format = new class implements FormatInterface {
            public function __toString(): string
            {
                return 'Y-m-d';
            }
        };
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new PointInTimeType($this->clock));
        $this->assertSame(
            'date<Y-m-d>',
            (string) new PointInTimeType(
                $this->clock,
                $this->format
            )
        );
        $this->assertSame(
            'date<Y-m-d\TH:i:sP>',
            (string) new PointInTimeType($this->clock)
        );
    }

    public function testDenormalize()
    {
        $t = new PointInTimeType($this->clock, $this->format);
        $this->assertInstanceOf(
            PointInTimeInterface::class,
            $t->denormalize('2016-01-01')
        );
        $this->assertSame(
            '160101',
            $t->denormalize('2016-01-01')->format(new class implements FormatInterface {
                public function __toString(): string
                {
                    return 'ymd';
                }
            })
        );
    }

    public function testThrowWhenNotDenormalizingADate()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('The value must be a point in time');

        (new PointInTimeType($this->clock))->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $type = new PointInTimeType($this->clock, new class implements FormatInterface {
            public function __toString(): string
            {
                return 'Y-m-d H:i:s';
            }
        });
        $this->assertSame(
            '2016-01-01 00:00:00',
            $type->normalize($this->clock->at('2016-01-01T00:00:00'))
        );
    }

    public function testThrowWhenNotNormalizingADate()
    {
        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('The value must be a point in time');

        (new PointInTimeType($this->clock))->normalize('foo');
    }
}