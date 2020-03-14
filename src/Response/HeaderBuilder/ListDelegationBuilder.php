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
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class ListDelegationBuilder implements ListBuilder
{
    private array $builders;

    public function __construct(ListBuilder ...$builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        SetInterface $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): SetInterface {
        if ((string) $identities->type() !== Identity::class) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type SetInterface<%s>',
                Identity::class
            ));
        }

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
