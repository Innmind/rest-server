<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    RangeExtractor\HeaderExtractor,
    RangeExtractor\ExtractorInterface,
    Request\Range
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Message\ResponseInterface,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Query\ParameterInterface as QueryParameterInterface,
    Message\Form,
    Message\Form\ParameterInterface as FormParameterInterface,
    Message\Files,
    File\FileInterface,
    Headers,
    Header\HeaderInterface,
    Header\HeaderValueInterface,
    Header\Accept,
    Header\AcceptValue,
    Header\ParameterInterface,
    Header\Range as RangeHeader,
    Header\RangeValue
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

        $this->assertInstanceOf(ExtractorInterface::class, $extractor);
    }

    public function testExtract()
    {
        $extractor = new HeaderExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            new Method('GET'),
            $protocol = new ProtocolVersion(1, 1),
            new Headers(
                (new Map('string', HeaderInterface::class))
                    ->put(
                        'Range',
                        new RangeHeader(
                            new RangeValue('resources', 0, 42)
                        )
                    )
            ),
            new StringStream(''),
            new Environment(new Map('string', 'scalar')),
            new Cookies(new Map('string', 'scalar')),
            new Query(new Map('string', QueryParameterInterface::class)),
            new Form(new Map('scalar', FormParameterInterface::class)),
            new Files(new Map('string', FileInterface::class))
        );

        $range = $extractor->extract($request);

        $this->assertInstanceOf(Range::class, $range);
        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\RangeNotFoundException
     */
    public function testThrowWhenRangeHeaderNotFound()
    {
        $extractor = new HeaderExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            new Method('GET'),
            $protocol = new ProtocolVersion(1, 1),
            new Headers(
                new Map('string', HeaderInterface::class)
            ),
            new StringStream(''),
            new Environment(new Map('string', 'scalar')),
            new Cookies(new Map('string', 'scalar')),
            new Query(new Map('string', QueryParameterInterface::class)),
            new Form(new Map('scalar', FormParameterInterface::class)),
            new Files(new Map('string', FileInterface::class))
        );

        $extractor->extract($request);
    }
}
