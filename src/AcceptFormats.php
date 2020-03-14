<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Format\Format,
    Format\MediaType,
};
use Innmind\Immutable\Set;

/**
 * Formats that can be rendered to the client
 */
final class AcceptFormats
{
    private static ?Formats $default = null;

    public static function default(): Formats
    {
        return self::$default ??= Formats::of(
            new Format(
                'json',
                Set::of(
                    MediaType::class,
                    new MediaType('application/json', 0)
                ),
                1
            )
        );
    }
}
