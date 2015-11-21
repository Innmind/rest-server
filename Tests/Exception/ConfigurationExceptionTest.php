<?php

namespace Innmind\Rest\Server\Tests\Exception;

use Innmind\Rest\Server\Exception\ConfigurationException;
use Innmind\Rest\Server\Exception\ExceptionInterface;

class ConfigurationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExceptionInterface::class,
            new ConfigurationException
        );
    }
}
