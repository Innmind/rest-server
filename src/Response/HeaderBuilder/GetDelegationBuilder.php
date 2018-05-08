<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Identity,
    HttpResource as HttpResourceInterface,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
};

final class GetDelegationBuilder implements GetBuilder
{
    private $builders;

    public function __construct(GetBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        HttpResourceInterface $resource,
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): MapInterface {
        $headers = new Map('string', Header::class);

        foreach ($this->builders as $build) {
            $headers = $headers->merge($build(
                $resource,
                $request,
                $definition,
                $identity
            ));
        }

        return $headers;
    }
}
