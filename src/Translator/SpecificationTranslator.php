<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Translator;

use Innmind\Specification\Specification;
use Innmind\Url\QueryInterface;

interface SpecificationTranslator
{
    public function __invoke(Specification $specification): QueryInterface;
}
