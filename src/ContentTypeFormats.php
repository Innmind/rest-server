<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Format\Format,
    Format\MediaType,
};
use Innmind\Immutable\Set;

/**
 * Content types allowed for the request
 */
final class ContentTypeFormats
{
    private static $default;

    public static function default(): Formats
    {
        return self::$default ?? self::$default = Formats::of(
            new Format(
                'json',
                Set::of(
                    MediaType::class,
                    new MediaType('application/json', 0)
                ),
                1
            ),
            new Format(
                'form',
                Set::of(
                    MediaType::class,
                    new MediaType('application/x-www-form-urlencoded', 1),
                    new MediaType('multipart/form-data', 0)
                ),
                0
            )
        );
    }
}
