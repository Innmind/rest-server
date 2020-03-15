<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Translator\Specification;

use Innmind\Rest\Server\{
    Translator\SpecificationTranslator,
    Exception\SpecificationNotUsableAsQuery,
};
use Innmind\Specification\{
    Specification,
    Comparator,
    Composite,
    Operator,
};
use Innmind\Url\Query;

final class DefaultTranslator implements SpecificationTranslator
{
    public function __invoke(Specification $specification): Query
    {
        $data = $this->extract($specification);

        return Query::of(\http_build_query($data));
    }

    /**
     * @return array<string, mixed>
     */
    private function extract(Specification $specification): array
    {
        /** @var array<string, mixed> */
        $data = [];

        switch (true) {
            case $specification instanceof Comparator:
                return [$specification->property() => $specification->value()];

            case $specification instanceof Composite:
                if ($specification->operator()->equals(Operator::or())) {
                    throw new SpecificationNotUsableAsQuery;
                }

                return \array_merge(
                    $this->extract($specification->left()),
                    $this->extract($specification->right())
                );

            default:
                throw new SpecificationNotUsableAsQuery;
        }
    }
}
