<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Reference,
    Exception\InvalidArgumentException
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface
};
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map
};

final class LinkDelegationBuilder implements LinkBuilderInterface
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== LinkBuilderInterface::class) {
            throw new InvalidArgumentException;
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        ServerRequestInterface $request,
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
                new Map('string', HeaderInterface::class),
                function(
                    MapInterface $carry,
                    LinkBuilderInterface $builder
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
