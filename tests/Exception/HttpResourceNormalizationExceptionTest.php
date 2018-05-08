<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Exception;

use Innmind\Rest\Server\Exception\HttpResourceNormalizationException;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class HttpResourceNormalizationExceptionTest extends TestCase
{
    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Exception\NormalizationException>
     */
    public function testThrowWhenBuildingWithInvalidMap()
    {
        new HttpResourceNormalizationException(new Map('string', 'string'));
    }
}
