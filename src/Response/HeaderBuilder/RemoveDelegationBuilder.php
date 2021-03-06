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
use Innmind\Immutable\Set;

final class RemoveDelegationBuilder implements RemoveBuilder
{
    /** @var list<RemoveBuilder> */
    private array $builders;

    public function __construct(RemoveBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): Set {
        /** @var Set<Header<Header\Value>> */
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
