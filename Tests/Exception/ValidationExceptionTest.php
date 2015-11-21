<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class ValidationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new ValidationException
        );
    }
}
