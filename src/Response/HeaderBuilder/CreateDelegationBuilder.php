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
use Innmind\Immutable\Set;

final class CreateDelegationBuilder implements CreateBuilder
{
    /** @var list<CreateBuilder> */
    private array $builders;

    public function __construct(CreateBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    public function __invoke(
        Identity $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): Set {
        /** @var Set<Header<Header\Value>> */
        $headers = Set::of(Header::class);

        foreach ($this->builders as $build) {
            $headers = $headers->merge($build(
                $identity,
                $request,
                $definition,
                $resource
            ));
        }

        return $headers;
    }
}
