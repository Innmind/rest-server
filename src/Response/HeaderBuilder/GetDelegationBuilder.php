<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Exception\InvalidArgumentException,
    IdentityInterface,
    HttpResourceInterface
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

final class GetDelegationBuilder implements GetBuilderInterface
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== GetBuilderInterface::class) {
            throw new InvalidArgumentException;
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        HttpResourceInterface $resource,
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
                    GetBuilderInterface $builder
                ) use (
                    $resource,
                    $request,
                    $definition,
                    $identity
                ): MapInterface {
                    return $carry->merge(
                        $builder->build(
                            $resource,
                            $request,
                            $definition,
                            $identity
                        )
                    );
                }
            );
    }
}
