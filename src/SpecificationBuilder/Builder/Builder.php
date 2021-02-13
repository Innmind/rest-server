<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\SpecificationBuilder\Builder;

use Innmind\Rest\Server\{
    SpecificationBuilder\Builder as BuilderInterface,
    Exception\NoFilterFound,
    Exception\FilterNotApplicable,
    Definition\HttpResource,
    Specification\Filter,
};
use Innmind\Http\Message\{
    ServerRequest,
    Query\Parameter,
};
use Innmind\Specification\Specification;

final class Builder implements BuilderInterface
{
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition
    ): Specification {
        $specification = $request->query()->reduce(
            null,
            static function(?Specification $specification, Parameter $parameter) use ($definition): ?Specification {
                if ($parameter->name() === 'range') {
                    // range is used in QueryExtractor to determine the range of the
                    // resources to return and thus can't be used as a filter
                    return $specification;
                }

                if (!$definition->properties()->contains($parameter->name())) {
                    throw new FilterNotApplicable($parameter->name());
                }

                if ($specification === null) {
                    $specification = new Filter(
                        $parameter->name(),
                        $parameter->value()
                    );
                } else {
                    $specification = $specification->and(new Filter(
                        $parameter->name(),
                        $parameter->value()
                    ));
                }

                return $specification;
            },
        );

        if ($specification === null) {
            throw new NoFilterFound;
        }

        return $specification;
    }
}
