<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\StringType,
    TypeInterface
};
use Innmind\Immutable\Collection;

class StringTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new StringType);
        $this->assertSame(
            ['string'],
            StringType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            StringType::class,
            StringType::fromConfig(new Collection([]))
        );
    }

    public function testDenormalize()
    {
        $this->assertSame(
            'foo',
            (new StringType)->denormalize(new class {
                public function __toString()
                {
                    return 'foo';
                }
            })
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be a string
     */
    public function testThrowWhenNotDenormalizingAString()
    {
        (new StringType)->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            'foo',
            (new StringType)->normalize(new class {
                public function __toString()
                {
                    return 'foo';
                }
            })
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\NormalizationException
     * @expectedExceptionMessage The value must be a string
     */
    public function testThrowWhenNotNormalizingAString()
    {
        (new StringType)->normalize(new \stdClass);
    }
}
