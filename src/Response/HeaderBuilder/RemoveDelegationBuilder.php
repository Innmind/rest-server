<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Exception\InvalidArgumentException,
    IdentityInterface
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

final class RemoveDelegationBuilder implements RemoveBuilderInterface
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== RemoveBuilderInterface::class) {
            throw new InvalidArgumentException;
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        ServerRequestInterface $request,
        HttpResource $definition,
        IdentityInterface $identity
    ): MapInterface {
        return $this
            ->builders
            ->reduce(
                new Map('string', HeaderInterface::class),
                function(
                    MapInterface $carry,
                    RemoveBuilderInterface $builder
                ) use (
                    $request,
                    $definition,
                    $identity
                ): MapInterface {
                    return $carry->merge(
                        $builder->build(
                            $request,
                            $definition,
                            $identity
                        )
                    );
                }
            );
    }
}
