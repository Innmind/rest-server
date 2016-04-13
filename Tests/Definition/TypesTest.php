<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests;

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
    Collection
};

class TypesTest extends \PHPUnit_Framework_TestCase
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

    public function testBuild()
    {
        $t = new Types;

        $this->assertInstanceOf(
            StringType::class,
            $t->build('string', new Collection([]))
        );
        $this->assertInstanceOf(
            SetType::class,
            $t->build('set', new Collection(['inner' => 'string']))
        );
    }
}
