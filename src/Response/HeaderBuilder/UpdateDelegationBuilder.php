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

final class UpdateDelegationBuilder implements UpdateBuilder
{
    /** @var list<UpdateBuilder> */
    private array $builders;

    public function __construct(UpdateBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity,
        HttpResourceInterface $resource
    ): Set {
        /** @var Set<Header<Header\Value>> */
        $headers = Set::of(Header::class);

        foreach ($this->builders as $build) {
            $headers = $headers->merge($build(
                $request,
                $definition,
                $identity,
                $resource
            ));
        }

        return $headers;
    }
}
