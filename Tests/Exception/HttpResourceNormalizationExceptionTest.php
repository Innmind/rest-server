<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Exception;

use Innmind\Rest\Server\Exception\HttpResourceNormalizationException;
use Innmind\Immutable\Map;

class HttpResourceNormalizationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenBuildingWithInvalidMap()
    {
        new HttpResourceNormalizationException(new Map('string', 'string'));
    }
}
