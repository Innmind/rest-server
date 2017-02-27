<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\DateType,
    TypeInterface,
    Types
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class DateTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new DateType);
        $this->assertSame(
            ['date'],
            DateType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            DateType::class,
            DateType::fromConfig(new Map('scalar', 'variable'), new Types)
        );
        $this->assertSame(
            'date<Y-m-d>',
            (string) DateType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('format', 'Y-m-d'),
                new Types
            )
        );
        $this->assertSame(
            'date<Y-m-d\TH:i:sO>',
            (string) DateType::fromConfig(
                new Map('scalar', 'variable'),
                new Types
            )
        );
    }

    public function testDenormalize()
    {
        $t = DateType::fromConfig(
            (new Map('scalar', 'variable'))
                ->put('format', 'Y-m-d'),
            new Types
        );
        $this->assertInstanceOf(
            \DateTimeImmutable::class,
            $t->denormalize('2016-01-01')
        );
        $this->assertSame(
            '160101',
            $t->denormalize('2016-01-01')->format('ymd')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be a date
     */
    public function testThrowWhenNotDenormalizingADate()
    {
        (new DateType)->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $t = DateType::fromConfig(
            (new Map('scalar', 'variable'))
                ->put('format', 'Y-m-d H:i:s'),
            new Types
        );
        $this->assertSame(
            '2016-01-01 00:00:00',
            $t->normalize('2016-01-01 00:00:00')
        );
        $this->assertSame(
            '2016-01-01 00:00:00',
            $t->normalize(new \DateTime('2016-01-01'))
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\NormalizationException
     * @expectedExceptionMessage The value must be a date
     */
    public function testThrowWhenNotNormalizingADate()
    {
        (new DateType)->normalize('foo');
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidConfigMap()
    {
        DateType::fromConfig(new Map('string', 'string'), new Types);
    }
}
