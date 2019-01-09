<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\Request\Range;
use Innmind\Http\Message\ServerRequest;

interface Extractor
{
    /**
     * Extract a Range out of the request
     *
     * @throws RangeNotFound
     */
    public function __invoke(ServerRequest $request): Range;
}
