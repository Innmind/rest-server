<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Identity,
    HttpResource as HttpResourceInterface
};
use Innmind\Http\{
    Message\ServerRequest,
    Header
};
use Innmind\Immutable\{
    MapInterface,
    Map
};

final class CreateDelegationBuilder implements CreateBuilder
{
    private $builders;

    public function __construct(CreateBuilder ...$builders)
    {
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
        $headers = new Map('string', Header::class);

        foreach ($this->builders as $builder) {
            $headers = $headers->merge($builder->build(
                $identity,
                $request,
                $definition,
                $resource
            ));
        }

        return $headers;
    }
}
