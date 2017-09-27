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

final class UpdateDelegationBuilder implements UpdateBuilder
{
    private $builders;

    public function __construct(UpdateBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity,
        HttpResourceInterface $resource
    ): MapInterface {
        $headers = new Map('string', Header::class);

        foreach ($this->builders as $builder) {
            $headers = $headers->merge($builder->build(
                $request,
                $definition,
                $identity,
                $resource
            ));
        }

        return $headers;
    }
}
