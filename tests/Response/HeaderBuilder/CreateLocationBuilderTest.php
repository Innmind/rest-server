<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateLocationBuilder,
    Response\HeaderBuilder\CreateBuilderInterface,
    Formats,
    Format\Format,
    Format\MediaType,
    Identity,
    HttpResourceInterface,
    Definition\HttpResource,
    Definition\Identity as IdentityDefinition,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\MethodInterface,
    ProtocolVersionInterface,
    Headers,
    Header\HeaderInterface,
    Header\HeaderValueInterface,
    Header\Accept,
    Header\AcceptValue,
    Header\ParameterInterface,
    Message\EnvironmentInterface,
    Message\CookiesInterface,
    Message\QueryInterface,
    Message\FormInterface,
    Message\FilesInterface
};
use Innmind\Url\Url;
use Innmind\Filesystem\StreamInterface;
use Innmind\Immutable\{
    Map,
    Set,
    Collection,
    MapInterface
};

class CreateLocationBuilderTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf(CreateBuilderInterface::class, $this->builder);
    }

    public function testBuild()
    {
        $headers = $this->builder->build(
            new Identity(42),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(MethodInterface::class),
                $this->createMock(ProtocolVersionInterface::class),
                new Headers(
                    (new Map('string', HeaderInterface::class))
                        ->put(
                            'Accept',
                            new Accept(
                                (new Set(HeaderValueInterface::class))
                                    ->add(new AcceptValue(
                                        'text',
                                        'xhtml',
                                        new Map('string', ParameterInterface::class)
                                    ))
                            )
                        )
                ),
                $this->createMock(StreamInterface::class),
                $this->createMock(EnvironmentInterface::class),
                $this->createMock(CookiesInterface::class),
                $this->createMock(QueryInterface::class),
                $this->createMock(FormInterface::class),
                $this->createMock(FilesInterface::class)
            ),
            new HttpResource(
                'foo',
                new IdentityDefinition('uuid'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                true
            ),
            $this->createMock(HttpResourceInterface::class)
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(HeaderInterface::class, (string) $headers->valueType());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Location : /foo/bar/42',
            (string) $headers->get('Location')
        );
    }
}
