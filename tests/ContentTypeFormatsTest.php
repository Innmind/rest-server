<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    ContentTypeFormats,
    Formats,
};
use PHPUnit\Framework\TestCase;

class ContentTypeFormatsTest extends TestCase
{
    public function testDefault()
    {
        $formats = ContentTypeFormats::default();

        $this->assertInstanceOf(Formats::class, $formats);
        $this->assertSame($formats, ContentTypeFormats::default());
        $this->assertCount(2, $formats->all());
        $this->assertTrue($formats->all()->contains('json'));
        $this->assertTrue($formats->all()->contains('form'));
    }
}
