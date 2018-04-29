<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Immutable\SetInterface;
use Symfony\Component\Serializer\{
    SerializerInterface,
    Serializer as SfSerializer
};

final class Serializer
{
    public static function make(
        SetInterface $encoders,
        SetInterface $normalizers
    ): SerializerInterface {
        return new SfSerializer(
            $normalizers->toPrimitive(),
            $encoders->toPrimitive()
        );
    }
}
