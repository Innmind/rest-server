<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Identity,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
};

final class RemoveDelegationBuilder implements RemoveBuilder
{
    private $builders;

    public function __construct(RemoveBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): MapInterface {
        $headers = new Map('string', Header::class);

        foreach ($this->builders as $build) {
            $headers = $headers->merge($build(
                $request,
                $definition,
                $identity
            ));
        }

        return $headers;
    }
}
