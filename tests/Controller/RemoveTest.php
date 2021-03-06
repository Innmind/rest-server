<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Remove,
    Controller,
    Gateway,
    Identity,
    Response\HeaderBuilder\RemoveBuilder,
    ResourceRemover,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Header,
    ProtocolVersion,
};
use Innmind\Immutable\{
    Map,
    Set,
};

class RemoveTest extends AbstractTestCase
{
    private $remove;
    private $gateway;
    private $headerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->remove = new Remove(
            Map::of('string', Gateway::class)
                ('foo', $this->gateway = $this->createMock(Gateway::class)),
            $this->headerBuilder = $this->createMock(RemoveBuilder::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Controller::class, $this->remove);
    }

    public function testThrowWhenInvalidGatewayKeyType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Remove(
            Map::of('int', Gateway::class),
            $this->createMock(RemoveBuilder::class)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Remove(
            Map::of('string', 'callable'),
            $this->createMock(RemoveBuilder::class)
        );
    }

    public function testInvokation()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));
        $identity = $this->createMock(Identity::class);
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceRemover')
            ->willReturn($remover = $this->createMock(ResourceRemover::class));
        $remover
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->definition, $identity);
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->willReturn(Set::of(Header::class));

        $response = ($this->remove)($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', $response->reasonPhrase()->toString());
        $this->assertSame('', $response->body()->toString());
    }
}
