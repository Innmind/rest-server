<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\SpecificationBuilder;

use Innmind\Rest\Server\{
    Exception\NoFilterFoundException,
    Exception\FilterNotApplicableException,
    Definition\HttpResource,
    Specification\Filter
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Specification\SpecificationInterface;

final class Builder implements BuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildFrom(
        ServerRequestInterface $request,
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
                throw new FilterNotApplicableException($parameter->name());
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
            throw new NoFilterFoundException;
        }

        return $specification;
    }
}