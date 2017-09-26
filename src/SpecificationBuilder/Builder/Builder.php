<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\SpecificationBuilder\Builder;

use Innmind\Rest\Server\{
    SpecificationBuilder\Builder as BuilderInterface,
    Exception\NoFilterFound,
    Exception\FilterNotApplicable,
    Definition\HttpResource,
    Specification\Filter
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Specification\SpecificationInterface;

final class Builder implements BuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildFrom(
        ServerRequest $request,
        HttpResource $definition
    ): SpecificationInterface {
        $specification = null;

        foreach ($request->query() as $parameter) {
            if ($parameter->name() === 'range') {
                /*
                range is used in QueryExtractor to determine the range of the
                resources to return and thus can't be used as a filter
                */
                continue;
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
        }

        if ($specification === null) {
            throw new NoFilterFound;
        }

        return $specification;
    }
}
