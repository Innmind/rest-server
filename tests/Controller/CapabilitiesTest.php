<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Capabilities,
    Router,
    Routing\Routes,
    Definition\Loader\YamlLoader,
    Definition\Types,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class CapabilitiesTest extends TestCase
{
    public function testInvokation()
    {
        $routes = Routes::from(
            (new YamlLoader(new Types))->load(
                Set::of('string', 'fixtures/mapping.yml')
            )
        );

        $capabilities = new Capabilities($routes, new Router($routes));

        $response = $capabilities($this->createMock(ServerRequest::class));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', (string) $response->reasonPhrase());
        $this->assertSame(1, $response->headers()->count());
        $this->assertSame(
            'Link : </top_dir/image/>; rel="top_dir.image", </top_dir/sub_dir/res/>; rel="top_dir.sub_dir.res"',
            (string) $response->headers()->get('Link')
        );
        $this->assertSame('', (string) $response->body());
    }
}
