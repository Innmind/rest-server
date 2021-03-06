<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    Identity,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Specification\Specification;
use Innmind\Immutable\Set;

final class ListDelegationBuilder implements ListBuilder
{
    /** @var list<ListBuilder> */
    private array $builders;

    public function __construct(ListBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    public function __invoke(
        Set $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): Set {
        if ($identities->type() !== Identity::class) {
            throw new \TypeError(\sprintf(
                'Argument 1 must be of type Set<%s>',
                Identity::class
            ));
        }

        /** @var Set<Header<Header\Value>> */
        $headers = Set::of(Header::class);

        foreach ($this->builders as $build) {
            $headers = $headers->merge($build(
                $identities,
                $request,
                $definition,
                $specification,
                $range
            ));
        }

        return $headers;
    }
}
