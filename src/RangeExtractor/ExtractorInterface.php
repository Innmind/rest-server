<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\Request\Range;
use Innmind\Http\Message\ServerRequestInterface;

interface ExtractorInterface
{
    /**
     * Extract a Range out of the request
     *
     * @param ServerRequestInterface $request
     *
     * @throws RangeNotFoundException
     *
     * @return Range
     */
    public function extract(ServerRequestInterface $request): Range;
}
