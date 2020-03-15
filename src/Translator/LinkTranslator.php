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
    Map,
    Set,
};
use function Innmind\Immutable\unwrap;

final class LinkTranslator
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return Set<Link>
     */
    public function __invoke(LinkHeader $link): Set
    {
        return $link->values()->reduce(
            Set::of(Link::class),
            function(Set $links, LinkValue $link): Set {
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
            ...unwrap($this->translateParameters($link->parameters())),
        );
    }

    /**
     * @return Set<Parameter>
     */
    private function translateParameters(Map $parameters): Set
    {
        return $parameters->reduce(
            Set::of(Parameter::class),
            static function(Set $parameters, string $name, HttpParameter $param): Set {
                return $parameters->add(
                    new Parameter\Parameter($name, $param->value())
                );
            }
        );
    }
}
