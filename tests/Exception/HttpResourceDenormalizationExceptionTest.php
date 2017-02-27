<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Exception;

use Innmind\Rest\Server\Exception\HttpResourceDenormalizationException;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class HttpResourceDenormalizationExceptionTest extends TestCase
{
    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenBuildingWithInvalidMap()
    {
        new HttpResourceDenormalizationException(new Map('string', 'string'));
    }
}
