<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Exception\InvalidArgumentException,
    Identity
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

final class RemoveDelegationBuilder implements RemoveBuilder
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== RemoveBuilder::class) {
            throw new InvalidArgumentException;
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): MapInterface {
        return $this
            ->builders
            ->reduce(
                new Map('string', Header::class),
                function(
                    MapInterface $carry,
                    RemoveBuilder $builder
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
