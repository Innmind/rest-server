<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    RangeExtractor\HeaderExtractor,
    RangeExtractor\Extractor,
    Request\Range,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
    Header\Accept,
    Header\AcceptValue,
    Header\Range as RangeHeader,
    Header\RangeValue,
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class HeaderExtractorTest extends TestCase
{
    public function testInterface()
    {
        $extractor = new HeaderExtractor;

        $this->assertInstanceOf(Extractor::class, $extractor);
    }

    public function testExtract()
    {
        $extract = new HeaderExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            Method::get(),
            $protocol = new ProtocolVersion(1, 1),
            Headers::of(
                new RangeHeader(
                    new RangeValue('resources', 0, 42)
                )
            )
        );

        $range = $extract($request);

        $this->assertInstanceOf(Range::class, $range);
        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\RangeNotFound
     */
    public function testThrowWhenRangeHeaderNotFound()
    {
        $extract = new HeaderExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            Method::get(),
            $protocol = new ProtocolVersion(1, 1)
        );

        $extract($request);
    }
}
