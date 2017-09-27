<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    RangeExtractor\DelegationExtractor,
    RangeExtractor\Extractor,
    Exception\RangeNotFound,
    Request\Range
};
use Innmind\Http\Message\ServerRequest;
use PHPUnit\Framework\TestCase;

class DelegationExtractorTest extends TestCase
{
    public function testInterface()
    {
        $extractor = new DelegationExtractor;

        $this->assertInstanceOf(Extractor::class, $extractor);
    }

    public function testExtract()
    {
        $extractor = new DelegationExtractor(
            $extractor1 = $this->createMock(Extractor::class),
            $extractor2 = $this->createMock(Extractor::class)
        );
        $extractor1
            ->method('extract')
            ->will($this->throwException(new RangeNotFound));
        $extractor2
            ->method('extract')
            ->willReturn($expected = new Range(0, 42));

        $range = $extractor->extract(
            $this->createMock(ServerRequest::class)
        );

        $this->assertSame($expected, $range);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\RangeNotFound
     */
    public function testThrowWhenRangeNotFound()
    {
        (new DelegationExtractor)->extract(
            $this->createMock(ServerRequest::class)
        );
    }
}
