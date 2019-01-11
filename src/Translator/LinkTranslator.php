<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Translator;

use Innmind\Rest\Server\{
    Router,
    Reference,
    Link,
    Link\Parameter,
};
use Innmind\Http\Header\{
    Link as LinkHeader,
    LinkValue,
    Parameter as HttpParameter,
};
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set,
};

final class LinkTranslator
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return SetInterface<Link>
     */
    public function __invoke(LinkHeader $link): SetInterface
    {
        return $link->values()->reduce(
            Set::of(Link::class),
            function(SetInterface $links, LinkValue $link): SetInterface {
                return $links->add($this->translateLinkValue($link));
            }
        );
    }

    private function translateLinkValue(LinkValue $link): Link
    {
        $match = $this->router->match($link->url()->path());

        return new Link(
            new Reference(
                $match->definition(),
                $match->identity()
            ),
            $link->relationship(),
            ...$this->translateParameters($link->parameters())
        );
    }

    /**
     * @return SetInterface<Parameter>
     */
    private function translateParameters(MapInterface $parameters): SetInterface
    {
        return $parameters->reduce(
            Set::of(Parameter::class),
            static function(SetInterface $parameters, string $name, HttpParameter $param): SetInterface {
                return $parameters->add(
                    new Parameter\Parameter($name, $param->value())
                );
            }
        );
    }
}
