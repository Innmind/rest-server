<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\UnknownPropertyAccessException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class UnknownPropertyAccessExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new UnknownPropertyAccessException
        );
    }
}
