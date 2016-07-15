<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Exception;

use Innmind\Rest\Server\Exception\HttpResourceDenormalizationException;
use Innmind\Immutable\Map;

class HttpResourceDenormalizationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenBuildingWithInvalidMap()
    {
        new HttpResourceDenormalizationException(new Map('string', 'string'));
    }
}
