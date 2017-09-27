<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\Reference;
use Innmind\Http\{
    Message\ServerRequest,
    Header
};
use Innmind\Immutable\{
    MapInterface,
    Map
};

final class LinkDelegationBuilder implements LinkBuilder
{
    private $builders;

    public function __construct(LinkBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        ServerRequest $request,
        Reference $from,
        MapInterface $tos
    ): MapInterface {
        if (
            (string) $tos->keyType() !== Reference::class ||
            (string) $tos->valueType() !== MapInterface::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type MapInterface<%s, %s>',
                Reference::class,
                MapInterface::class
            ));
        }

        $headers = new Map('string', Header::class);

        foreach ($this->builders as $builder) {
            $headers = $headers->merge($builder->build(
                $request,
                $from,
                $tos
            ));
        }

        return $headers;
    }
}
