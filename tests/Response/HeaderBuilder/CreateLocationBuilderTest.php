<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateLocationBuilder,
    Response\HeaderBuilder\CreateBuilder,
    Identity\Identity,
    HttpResource,
    Definition\Loader\YamlLoader,
    Router,
    Routing\Routes,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Header\Parameter,
};
use Innmind\Url\Url;
use Innmind\Immutable\MapInterface;
use PHPUnit\Framework\TestCase;

class CreateLocationBuilderTest extends TestCase
{
    private $build;
    private $directories;

    public function setUp()
    {
        $this->build = new CreateLocationBuilder(
            new Router(
                Routes::from(
                    $this->directories = (new YamlLoader)('fixtures/mapping.yml')
                )
            )
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(CreateBuilder::class, $this->build);
    }

    public function testBuild()
    {
        $headers = ($this->build)(
            new Identity(42),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class),
                Headers::of(
                    new Accept(
                        new AcceptValue('text', 'xhtml')
                    )
                )
            ),
            $this->directories->get('top_dir')->definition('image'),
            $this->createMock(HttpResource::class)
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Location : /top_dir/image/42',
            (string) $headers->get('Location')
        );
    }
}
