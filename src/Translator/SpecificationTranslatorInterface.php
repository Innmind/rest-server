<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Translator;

use Innmind\Specification\SpecificationInterface;
use Innmind\Url\QueryInterface;

interface SpecificationTranslatorInterface
{
    public function translate(SpecificationInterface $specification): QueryInterface;
}
