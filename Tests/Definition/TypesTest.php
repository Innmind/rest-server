<?php

namespace Innmind\Rest\Server\Tests\Definition;

use Innmind\Rest\Server\Definition\Types;
use Innmind\Rest\Server\Definition\Type\IntType;

class TypesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown type "foo"
     */
    public function testThrowWhenUnknownType()
    {
        Types::get('foo');
    }

    public function testHas()
    {
        $this->assertTrue(Types::has('int'));
    }

    public function testHasnt()
    {
        $this->assertFalse(Types::has('foo'));
    }

    public function testGet()
    {
        $this->assertInstanceOf(
            IntType::class,
            Types::get('int')
        );
    }
}
