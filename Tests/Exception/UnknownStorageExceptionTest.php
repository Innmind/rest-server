<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\UnknownStorageException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class UnknownStorageExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new UnknownStorageException
        );
    }
}
