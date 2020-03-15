<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Translator;

use Innmind\Rest\Server\{
    Translator\LinkTranslator,
    Definition\Loader\YamlLoader,
    Link,
    Link\Parameter,
    Reference,
    Router,
    Routing\Routes,
};
use Innmind\Http\Header\{
    Link as LinkHeader,
    LinkValue,
    Parameter as LinkParameterInterface,
    Parameter\Parameter as LinkParameter,
    Value,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Map,
};
use function Innmind\Immutable\first;
use PHPUnit\Framework\TestCase;

class LinkTranslatorTest extends TestCase
{
    public function testTranslate()
    {
        $translate = new LinkTranslator(
            new Router(
                Routes::from(
                    $directory = require 'fixtures/mapping.php'
                )
            )
        );

        $links = $translate(
            new LinkHeader(
                new LinkValue(
                    Url::of('/top_dir/sub_dir/res/bar'),
                    'relationship_name',
                    new LinkParameter('foo', 'baz'),
                )
            )
        );

        $this->assertInstanceOf(Set::class, $links);
        $this->assertSame(Link::class, (string) $links->type());
        $this->assertCount(1, $links);
        $link = first($links);
        $this->assertSame(
            $directory->child('sub_dir')->definition('res'),
            $link->reference()->definition()
        );
        $this->assertSame(
            'bar',
            $link->reference()->identity()->toString(),
        );
        $this->assertTrue($link->has('foo'));
        $this->assertSame('baz', $link->get('foo')->value());
        $this->assertSame('relationship_name', $link->relationship());
    }
}
