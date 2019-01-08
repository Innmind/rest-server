<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\DateType,
    Type,
    Types,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class DateTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new DateType);
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
                Map::of('scalar', 'variable')
                    ('format', 'Y-m-d'),
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
            Map::of('scalar', 'variable')
                ('format', 'Y-m-d'),
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
        $type = DateType::fromConfig(
            Map::of('scalar', 'variable')
                ('format', 'Y-m-d H:i:s'),
            new Types
        );
        $this->assertSame(
            '2016-01-01 00:00:00',
            $type->normalize('2016-01-01 00:00:00')
        );
        $this->assertSame(
            '2016-01-01 00:00:00',
            $type->normalize(new \DateTime('2016-01-01'))
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<scalar, variable>
     */
    public function testThrowWhenInvalidConfigMap()
    {
        DateType::fromConfig(new Map('string', 'string'), new Types);
    }
}
