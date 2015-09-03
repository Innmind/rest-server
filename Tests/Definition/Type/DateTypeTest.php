<?php

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\Type\DateType;
use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\Validator\Constraints\Callback;

class DateTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConstraints()
    {
        $t = new DateType;
        $p = new Property('foo');

        $this->assertSame(
            1,
            count($t->getConstraints($p))
        );
        $this->assertInstanceOf(
            Callback::class,
            $t->getConstraints($p)[0]
        );
    }
}
