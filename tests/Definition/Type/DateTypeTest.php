<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type\DateType,
    Definition\Type,
    Exception\NormalizationException,
    Exception\DenormalizationException,
};
use PHPUnit\Framework\TestCase;

class DateTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new DateType);
        $this->assertSame(
            'date<Y-m-d>',
            (string) new DateType('Y-m-d')
        );
        $this->assertSame(
            'date<Y-m-d\TH:i:sO>',
            (string) new DateType
        );
    }

    public function testDenormalize()
    {
        $t = new DateType('Y-m-d');
        $this->assertInstanceOf(
            \DateTimeImmutable::class,
            $t->denormalize('2016-01-01')
        );
        $this->assertSame(
            '160101',
            $t->denormalize('2016-01-01')->format('ymd')
        );
    }

    public function testThrowWhenNotDenormalizingADate()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('The value must be a date');

        (new DateType)->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $type = new DateType('Y-m-d H:i:s');
        $this->assertSame(
            '2016-01-01 00:00:00',
            $type->normalize('2016-01-01 00:00:00')
        );
        $this->assertSame(
            '2016-01-01 00:00:00',
            $type->normalize(new \DateTime('2016-01-01'))
        );
    }

    public function testThrowWhenNotNormalizingADate()
    {
        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('The value must be a date');

        (new DateType)->normalize('foo');
    }
}
