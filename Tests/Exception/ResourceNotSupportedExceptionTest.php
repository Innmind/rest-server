<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\ResourceNotSupportedException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class ResourceNotSupportedExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new ResourceNotSupportedException
        );
    }
}
