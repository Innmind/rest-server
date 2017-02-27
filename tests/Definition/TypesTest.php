<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\{
    Types,
    TypeInterface,
    Type\SetType,
    Type\MapType,
    Type\BoolType,
    Type\DateType,
    Type\FloatType,
    Type\IntType,
    Type\StringType
};
use Innmind\Immutable\{
    Set,
    Map
};
use PHPUnit\Framework\TestCase;

class TypesTest extends TestCase
{
    public function testAll()
    {
        $t = new Types;

        $this->assertSame(
            [
                'set',
                'map',
                'bool',
                'boolean',
                'date',
                'float',
                'int',
                'integer',
                'string',
            ],
            $t->all()->keys()->toPrimitive()
        );
        $this->assertSame(
            [
                SetType::class,
                MapType::class,
                BoolType::class,
                BoolType::class,
                DateType::class,
                FloatType::class,
                IntType::class,
                IntType::class,
                StringType::class,
            ],
            $t->all()->values()->toPrimitive()
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     * @expectedExceptionMessage The type "stdClass" must implement TypeInterface
     */
    public function testThrowWhenRegisteringingInvalidType()
    {
        (new Types)->register('stdClass');
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidConfigMap()
    {
        (new Types)->build('string', new Map('string', 'string'));
    }

    public function testBuild()
    {
        $t = new Types;

        $this->assertInstanceOf(
            StringType::class,
            $t->build('string', new Map('scalar', 'variable'))
        );
        $this->assertInstanceOf(
            SetType::class,
            $t->build(
                'set',
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string')
            )
        );
    }
}
