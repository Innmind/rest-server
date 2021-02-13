<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    RangeExtractor\HeaderExtractor,
    RangeExtractor\Extractor,
    Request\Range,
    Exception\RangeNotFound,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Headers,
    Header\Accept,
    Header\AcceptValue,
    Header\Range as RangeHeader,
    Header\RangeValue,
};
use Innmind\Url\Url;
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
            Url::of('/'),
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

    public function testThrowWhenRangeHeaderNotFound()
    {
        $extract = new HeaderExtractor;
        $request = new ServerRequest(
            Url::of('/'),
            Method::get(),
            $protocol = new ProtocolVersion(1, 1)
        );

        $this->expectException(RangeNotFound::class);

        $extract($request);
    }
}
