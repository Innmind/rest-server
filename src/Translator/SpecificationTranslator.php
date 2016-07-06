<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Translator;

use Innmind\Rest\Server\Exception\SpecificationNotUsableAsQueryException;
use Innmind\Specification\{
    SpecificationInterface,
    ComparatorInterface,
    CompositeInterface,
    Operator
};
use Innmind\Url\{
    QueryInterface,
    Query
};

final class SpecificationTranslator implements SpecificationTranslatorInterface
{
    public function translate(SpecificationInterface $specification): QueryInterface
    {
        $data = $this->extract($specification);

        return new Query(http_build_query($data));
    }

    private function extract(SpecificationInterface $specification): array
    {
        $data = [];

        switch (true) {
            case $specification instanceof ComparatorInterface:
                $data[$specification->property()] = $specification->value();
                break;
            case $specification instanceof CompositeInterface:
                if ((string) $specification->operator() === Operator::OR) {
                    throw new SpecificationNotUsableAsQueryException;
                }

                $data = array_merge(
                    $data,
                    $this->extract($specification->left()),
                    $this->extract($specification->right())
                );
                break;
            default:
                throw new SpecificationNotUsableAsQueryException;
        }

        return $data;
    }
}
