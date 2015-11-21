<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class ResourceNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new ResourceNotFoundException
        );
    }
}
