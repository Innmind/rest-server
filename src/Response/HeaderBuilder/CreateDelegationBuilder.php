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

final class CreateDelegationBuilder implements CreateBuilder
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== CreateBuilder::class) {
            throw new InvalidArgumentException;
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        Identity $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): MapInterface {
        return $this
            ->builders
            ->reduce(
                new Map('string', Header::class),
                function(
                    MapInterface $carry,
                    CreateBuilder $builder
                ) use (
                    $identity,
                    $request,
                    $definition,
                    $resource
                ): MapInterface {
                    return $carry->merge(
                        $builder->build(
                            $identity,
                            $request,
                            $definition,
                            $resource
                        )
                    );
                }
            );
    }
}
