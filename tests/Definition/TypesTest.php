<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    Types,
    Type,
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
        $types = new Types;

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
            $types->all()->keys()->toPrimitive()
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
            $types->all()->values()->toPrimitive()
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DomainException
     * @expectedExceptionMessage The type "stdClass" must implement Type
     */
    public function testThrowWhenRegisteringingInvalidType()
    {
        new Types('stdClass');
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 2 must be of type MapInterface<scalar, variable>
     */
    public function testThrowWhenInvalidConfigMap()
    {
        (new Types)->build('string', new Map('string', 'string'));
    }

    public function testBuild()
    {
        $types = new Types;

        $this->assertInstanceOf(
            StringType::class,
            $types->build('string', new Map('scalar', 'variable'))
        );
        $this->assertInstanceOf(
            SetType::class,
            $types->build(
                'set',
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string')
            )
        );
    }
}
