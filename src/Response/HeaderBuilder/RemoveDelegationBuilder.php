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
    SetInterface,
    Set,
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
    ): SetInterface {
        $headers = Set::of(Header::class);

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
