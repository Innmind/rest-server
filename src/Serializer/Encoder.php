<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer;

use Innmind\Http\Message\ServerRequest;
use Innmind\Stream\Readable;

interface Encoder
{
    public function __invoke(ServerRequest $request, array $data): Readable;
}
