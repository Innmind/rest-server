<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Translator;

use Innmind\Rest\Server\{
    Translator\LinkTranslator,
    Definition\Loader\YamlLoader,
    Definition\Types,
    Link\Parameter,
    Reference,
    Router,
    Routing\Routes,
};
use Innmind\Http\Header\{
    Link,
    LinkValue,
    Parameter as LinkParameterInterface,
    Parameter\Parameter as LinkParameter,
    Value,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    MapInterface
};
use PHPUnit\Framework\TestCase;

class LinkTranslatorTest extends TestCase
{
    public function testTranslate()
    {
        $translate = new LinkTranslator(
            new Router(
                Routes::from(
                    $directories = (new YamlLoader(new Types))('fixtures/mapping.yml')
                )
            )
        );

        $references = $translate(
            new Link(
                new LinkValue(
                    Url::fromString('/top_dir/sub_dir/res/bar'),
                    'relationship',
                    (new Map('string', LinkParameterInterface::class))
                        ->put('foo', new LinkParameter('foo', 'baz'))
                )
            )
        );

        $this->assertInstanceOf(MapInterface::class, $references);
        $this->assertSame(Reference::class, (string) $references->keyType());
        $this->assertSame(MapInterface::class, (string) $references->valueType());
        $this->assertCount(1, $references);
        $this->assertSame(
            $directories->get('top_dir')->child('sub_dir')->definition('res'),
            $references->keys()->current()->definition()
        );
        $this->assertSame(
            'bar',
            (string) $references->keys()->current()->identity()
        );
        $parameters = $references->values()->first();
        $this->assertSame('string', (string) $parameters->keyType());
        $this->assertSame(
            Parameter::class,
            (string) $parameters->valueType()
        );
        $this->assertCount(2, $parameters);
        $this->assertSame(
            ['foo', 'rel'],
            $parameters->keys()->toPrimitive()
        );
        $this->assertSame('baz', $parameters->get('foo')->value());
        $this->assertSame('relationship', $parameters->get('rel')->value());
    }
}
