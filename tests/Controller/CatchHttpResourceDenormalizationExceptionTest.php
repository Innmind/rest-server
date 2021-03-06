<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\CatchHttpResourceDenormalizationException,
    Controller,
    Identity,
    Definition,
    Exception\HttpResourceDenormalizationException,
    Exception\DenormalizationException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    ProtocolVersion,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class CatchHttpResourceDenormalizationExceptionTest extends TestCase
{
    private $definition;

    public function setUp(): void
    {
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Gateway('foo'),
            new Definition\Identity('foo'),
            Set::of(Definition\Property::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            new CatchHttpResourceDenormalizationException($this->createMock(Controller::class))
        );
    }

    public function testReturnResponseWhenHttpResourceDenormalizationException()
    {
        $catch = new CatchHttpResourceDenormalizationException(
            $controller = $this->createMock(Controller::class)
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->once())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));
        $identity = $this->createMock(Identity::class);
        $controller
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->will($this->throwException(new HttpResourceDenormalizationException(
                Map::of('string', DenormalizationException::class)
            )));

        $response = $catch($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->statusCode()->value());
        $this->assertSame('Bad Request', $response->reasonPhrase()->toString());
    }

    public function testReturnControllerResponse()
    {
        $catch = new CatchHttpResourceDenormalizationException(
            $controller = $this->createMock(Controller::class)
        );
        $request = $this->createMock(ServerRequest::class);
        $identity = $this->createMock(Identity::class);
        $controller
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->willReturn($expected = $this->createMock(Response::class));

        $this->assertSame(
            $expected,
            $catch($request, $this->definition, $identity)
        );
    }
}
