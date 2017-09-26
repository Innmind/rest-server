<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Reference,
    Exception\InvalidArgumentException
};
use Innmind\Http\{
    Message\ServerRequest,
    Header
};
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map
};

final class LinkDelegationBuilder implements LinkBuilder
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== LinkBuilder::class) {
            throw new InvalidArgumentException;
        }

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
            throw new InvalidArgumentException;
        }

        return $this
            ->builders
            ->reduce(
                new Map('string', Header::class),
                function(
                    MapInterface $carry,
                    LinkBuilder $builder
                ) use (
                    $request,
                    $from,
                    $tos
                ): MapInterface {
                    return $carry->merge(
                        $builder->build(
                            $request,
                            $from,
                            $tos
                        )
                    );
                }
            );
    }
}
