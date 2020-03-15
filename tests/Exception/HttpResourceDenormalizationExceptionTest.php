<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Exception;

use Innmind\Rest\Server\Exception\HttpResourceDenormalizationException;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class HttpResourceDenormalizationExceptionTest extends TestCase
{
    public function testThrowWhenBuildingWithInvalidMap()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<string, Innmind\Rest\Server\Exception\DenormalizationException>');

        new HttpResourceDenormalizationException(Map::of('string', 'string'));
    }
}
