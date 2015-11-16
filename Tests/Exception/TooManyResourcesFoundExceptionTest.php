<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class TooManyResourcesFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new TooManyResourcesFoundException
        );
    }
}
