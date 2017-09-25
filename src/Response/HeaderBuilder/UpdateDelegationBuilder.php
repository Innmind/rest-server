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
    Message\ServerRequest,
    Header
};
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map
};

final class UpdateDelegationBuilder implements UpdateBuilderInterface
{
    private $builders;

    public function __construct(SetInterface $builders)
    {
        if ((string) $builders->type() !== UpdateBuilderInterface::class) {
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
        IdentityInterface $identity,
        HttpResourceInterface $resource
    ): MapInterface {
        return $this
            ->builders
            ->reduce(
                new Map('string', Header::class),
                function(
                    MapInterface $carry,
                    UpdateBuilderInterface $builder
                ) use (
                    $request,
                    $definition,
                    $identity,
                    $resource
                ): MapInterface {
                    return $carry->merge(
                        $builder->build(
                            $request,
                            $definition,
                            $identity,
                            $resource
                        )
                    );
                }
            );
    }
}
