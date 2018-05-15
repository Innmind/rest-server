<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer;

use Innmind\Http\Message\ServerRequest;

interface RequestDecoder
{
    public function __invoke(ServerRequest $request): array;
}
