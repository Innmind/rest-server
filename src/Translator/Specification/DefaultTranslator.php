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
use Innmind\Url\{
    QueryInterface,
    Query,
};

final class DefaultTranslator implements SpecificationTranslator
{
    public function __invoke(Specification $specification): QueryInterface
    {
        $data = $this->extract($specification);

        return new Query(\http_build_query($data));
    }

    private function extract(Specification $specification): array
    {
        $data = [];

        switch (true) {
            case $specification instanceof Comparator:
                $data[$specification->property()] = $specification->value();
                break;
            case $specification instanceof Composite:
                if ($specification->operator()->equals(Operator::or())) {
                    throw new SpecificationNotUsableAsQuery;
                }

                $data = \array_merge(
                    $data,
                    $this->extract($specification->left()),
                    $this->extract($specification->right())
                );
                break;
            default:
                throw new SpecificationNotUsableAsQuery;
        }

        return $data;
    }
}
