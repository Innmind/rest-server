<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\DateType,
    TypeInterface
};
use Innmind\Immutable\Collection;

class DateTypeTest extends \PHPUnit_Framework_TestCase
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
            DateType::fromConfig(new Collection([]))
        );
    }

    public function testDenormalize()
    {
        $t = DateType::fromConfig(new Collection([
            'format' => 'Y-m-d',
        ]));
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
        $t = DateType::fromConfig(new Collection([
            'format' => 'Y-m-d H:i:s',
        ]));
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
}
