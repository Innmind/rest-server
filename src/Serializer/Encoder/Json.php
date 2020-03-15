<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\Encoder;
use Innmind\Http\Message\ServerRequest;
use Innmind\Stream\Readable;
use Innmind\Stream\Readable\Stream;
use Innmind\Json\Json as Helper;

final class Json implements Encoder
{
    public function __invoke(ServerRequest $request, array $data): Readable
    {
        return Stream::ofContent(
            Helper::encode($data)
        );
    }
}
