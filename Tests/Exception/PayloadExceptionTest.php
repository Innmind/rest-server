<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class PayloadExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new PayloadException
        );
    }
}
