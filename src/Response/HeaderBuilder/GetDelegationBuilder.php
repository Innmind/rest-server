<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Exception\InvalidArgumentException,
    Identity,
    HttpResource as HttpResourceInterface
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

final class GetDelegationBuilder implements GetBuilder
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== GetBuilder::class) {
            throw new InvalidArgumentException;
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        HttpResourceInterface $resource,
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
                    GetBuilder $builder
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
