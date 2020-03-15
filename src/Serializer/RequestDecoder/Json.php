<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\RequestDecoder;

use Innmind\Rest\Server\Serializer\RequestDecoder;
use Innmind\Http\Message\ServerRequest;
use Innmind\Json\Json as Parser;

final class Json implements RequestDecoder
{
    public function __invoke(ServerRequest $request): array
    {
        return Parser::decode($request->body()->toString());
    }
}
