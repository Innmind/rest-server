<?php

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\Type\FloatType;
use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\Validator\Constraints\Type;

class FloatTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConstraints()
    {
        $t = new FloatType;
        $p = new Property('foo');

        $this->assertSame(
            1,
            count($t->getConstraints($p))
        );
        $this->assertInstanceOf(
            Type::class,
            $t->getConstraints($p)[0]
        );
        $this->assertSame(
            'float',
            $t->getConstraints($p)[0]->type
        );
    }
}
