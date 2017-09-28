<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Translator;

use Innmind\Specification\SpecificationInterface;
use Innmind\Url\QueryInterface;

interface SpecificationTranslator
{
    public function __invoke(SpecificationInterface $specification): QueryInterface;
}
