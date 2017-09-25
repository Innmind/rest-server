<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    Exception\InvalidArgumentException,
    IdentityInterface
};
use Innmind\Http\{
    Message\ServerRequest,
    Header
};
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map
};

final class ListDelegationBuilder implements ListBuilderInterface
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== ListBuilderInterface::class) {
            throw new InvalidArgumentException;
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        SetInterface $identities,
        ServerRequest $request,
        HttpResource $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): MapInterface {
        if ((string) $identities->type() !== IdentityInterface::class) {
            throw new InvalidArgumentException;
        }

        return $this
            ->builders
            ->reduce(
                new Map('string', Header::class),
                function(
                    MapInterface $carry,
                    ListBuilderInterface $builder
                ) use (
                    $identities,
                    $request,
                    $definition,
                    $specification,
                    $range
                ): MapInterface {
                    return $carry->merge(
                        $builder->build(
                            $identities,
                            $request,
                            $definition,
                            $specification,
                            $range
                        )
                    );
                }
            );
    }
}
