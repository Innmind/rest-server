<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Link\Parameter;
use Innmind\Immutable\{
    Sequence,
    MapInterface,
    Map,
};

final class Link
{
    private $reference;
    private $parameters;

    public function __construct(Reference $reference, Parameter ...$parameters)
    {
        $this->reference = $reference;
        $this->parameters = Sequence::of(...$parameters)->reduce(
            Map::of('string', Parameter::class),
            static function(MapInterface $parameters, Parameter $parameter): MapInterface {
                return $parameters->put(
                    $parameter->name(),
                    $parameter
                );
            }
        );
    }

    public function reference(): Reference
    {
        return $this->reference;
    }

    public function has(string $parameter): bool
    {
        return $this->parameters->contains($parameter);
    }

    public function get(string $parameter): Parameter
    {
        return $this->parameters->get($parameter);
    }
}
