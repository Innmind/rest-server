<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Reference,
    Link,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Immutable\Set;

final class LinkDelegationBuilder implements LinkBuilder
{
    /** @var list<LinkBuilder> */
    private array $builders;

    public function __construct(LinkBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    public function __invoke(
        ServerRequest $request,
        Reference $from,
        Link ...$links
    ): Set {
        /** @var Set<Header<Header\Value>> */
        $headers = Set::of(Header::class);

        foreach ($this->builders as $build) {
            $headers = $headers->merge($build(
                $request,
                $from,
                ...$links
            ));
        }

        return $headers;
    }
}
