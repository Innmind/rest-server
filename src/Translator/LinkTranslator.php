<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Translator;

use Innmind\Rest\Server\{
    Router,
    Reference,
    Link\Parameter,
};
use Innmind\Http\Header\{
    Link,
    LinkValue,
    Parameter as HttpParameter
};
use Innmind\Immutable\{
    MapInterface,
    Map
};

final class LinkTranslator
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return MapInterface<Reference, MapInterface<string, Parameter>>
     */
    public function __invoke(Link $link): MapInterface
    {
        return $link->values()->reduce(
            new Map(Reference::class, MapInterface::class),
            function(MapInterface $carry, LinkValue $link): MapInterface {
                [$reference, $parameters] = $this->translateLinkValue($link);
                return $carry->put($reference, $parameters);
            }
        );
    }

    /**
     * @return array<Reference, MapInterface<string, Parameter>>
     */
    private function translateLinkValue(LinkValue $link): array
    {
        $match = $this->router->match($link->url()->path());

        return [
            new Reference(
                $match->definition(),
                $match->identity()
            ),
            $this
                ->translateParameters($link->parameters())
                ->put('rel', new Parameter\Parameter('rel', $link->relationship()))
        ];
    }

    /**
     * @return MapInterface<string, Parameter>
     */
    private function translateParameters(MapInterface $parameters): MapInterface
    {
        return $parameters->reduce(
            new Map('string', Parameter::class),
            static function(MapInterface $carry, string $name, HttpParameter $param): MapInterface {
                return $carry->put(
                    $name,
                    new Parameter\Parameter($name, $param->value())
                );
            }
        );
    }
}
