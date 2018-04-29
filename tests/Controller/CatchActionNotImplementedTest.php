<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\CatchActionNotImplemented,
    Controller,
    Identity,
    Definition,
    Exception\ActionNotImplemented
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class CatchActionNotImplementedTest extends TestCase
{
    private $definition;

    public function setUp()
    {
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Identity('foo'),
            new Map('string', Definition\Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Definition\Gateway('foo'),
            false,
            new Map('string', 'string')
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            new CatchActionNotImplemented($this->createMock(Controller::class))
        );
    }

    public function testReturnResponseWhenActionNotImplemented()
    {
        $catch = new CatchActionNotImplemented(
            $controller = $this->createMock(Controller::class)
        );
        $request = $this->createMock(ServerRequest::class);
        $identity = $this->createMock(Identity::class);
        $controller
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->will($this->throwException(new ActionNotImplemented));

        $response = $catch($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(405, $response->statusCode()->value());
        $this->assertSame('Method Not Allowed', (string) $response->reasonPhrase());
    }

    public function testReturnControllerResponse()
    {
        $catch = new CatchActionNotImplemented(
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
