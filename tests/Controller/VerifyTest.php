<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Verify,
    Controller,
    Identity,
    Definition,
    Request\Verifier\Verifier,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class VerifyTest extends TestCase
{
    private $definition;

    public function setUp(): void
    {
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Gateway('foo'),
            new Definition\Identity('foo'),
            new Set(Definition\Property::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            new Verify(
                $this->createMock(Verifier::class),
                $this->createMock(Controller::class)
            )
        );
    }

    public function testThrowWhenNotVerified()
    {
        $verify = new Verify(
            $verifier = $this->createMock(Verifier::class),
            $controller = $this->createMock(Controller::class)
        );
        $request = $this->createMock(ServerRequest::class);
        $controller
            ->expects($this->never())
            ->method('__invoke');
        $verifier
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition)
            ->will($this->throwException(new \Error));

        $this->expectException(\Error::class);

        $verify($request, $this->definition);
    }

    public function testCallController()
    {
        $verify = new Verify(
            $verifier = $this->createMock(Verifier::class),
            $controller = $this->createMock(Controller::class)
        );
        $request = $this->createMock(ServerRequest::class);
        $identity = $this->createMock(Identity::class);
        $controller
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->willReturn($expected = $this->createMock(Response::class));
        $verifier
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition);

        $this->assertSame(
            $expected,
            $verify($request, $this->definition, $identity)
        );
    }
}
