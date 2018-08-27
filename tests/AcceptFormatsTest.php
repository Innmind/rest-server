<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    AcceptFormats,
    Formats,
};
use PHPUnit\Framework\TestCase;

class AcceptFormatsTest extends TestCase
{
    public function testDefault()
    {
        $formats = AcceptFormats::default();

        $this->assertInstanceOf(Formats::class, $formats);
        $this->assertSame($formats, AcceptFormats::default());
        $this->assertCount(1, $formats->all());
        $this->assertTrue($formats->all()->contains('json'));
    }
}
