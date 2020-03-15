<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Capabilities,
    Router,
    Routing\Routes,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    ProtocolVersion,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class CapabilitiesTest extends TestCase
{
    public function testInvokation()
    {
        $routes = Routes::from(
            require 'fixtures/mapping.php'
        );

        $capabilities = new Capabilities($routes, new Router($routes));
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->once())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));

        $response = $capabilities($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', $response->reasonPhrase()->toString());
        $this->assertCount(1, $response->headers());
        $this->assertSame(
            'Link: </top_dir/image/>; rel="top_dir.image", </top_dir/sub_dir/res/>; rel="top_dir.sub_dir.res"',
            $response->headers()->get('Link')->toString(),
        );
        $this->assertSame('', $response->body()->toString());
    }
}
