<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateLocationBuilder,
    Response\HeaderBuilder\CreateBuilder,
    Formats,
    Format\Format,
    Format\MediaType,
    Identity\Identity,
    HttpResource as HttpResourceInterface,
    Definition\HttpResource,
    Definition\Identity as IdentityDefinition,
    Definition\Property,
    Definition\Gateway
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
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Form,
    Message\Files
};
use Innmind\Url\Url;
use Innmind\Stream\Readable;
use Innmind\Immutable\{
    Map,
    Set,
    MapInterface
};
use PHPUnit\Framework\TestCase;

class CreateLocationBuilderTest extends TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = new CreateLocationBuilder(
            new Formats(
                (new Map('string', Format::class))
                    ->put(
                        'json',
                        new Format(
                            'json',
                            (new Set(MediaType::class))
                                ->add(new MediaType('application/json', 42)),
                            42
                        )
                    )
                    ->put(
                        'html',
                        new Format(
                            'html',
                            (new Set(MediaType::class))
                                ->add(new MediaType('text/html', 40))
                                ->add(new MediaType('text/xhtml', 0)),
                            0
                        )
                    )
            )
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(CreateBuilder::class, $this->builder);
    }

    public function testBuild()
    {
        $headers = $this->builder->build(
            new Identity(42),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'Accept',
                            new Accept(
                                new AcceptValue(
                                    'text',
                                    'xhtml',
                                    new Map('string', Parameter::class)
                                )
                            )
                        )
                ),
                $this->createMock(Readable::class),
                $this->createMock(Environment::class),
                $this->createMock(Cookies::class),
                $this->createMock(Query::class),
                $this->createMock(Form::class),
                $this->createMock(Files::class)
            ),
            new HttpResource(
                'foo',
                new IdentityDefinition('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            ),
            $this->createMock(HttpResourceInterface::class)
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Location : /foo/bar/42',
            (string) $headers->get('Location')
        );
    }
}
