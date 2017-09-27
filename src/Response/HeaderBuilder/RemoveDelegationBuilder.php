<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
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
            throw new \TypeError(sprintf(
                'Argument 1 must be of type SetInterface<%s>',
                RemoveBuilder::class
            ));
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
