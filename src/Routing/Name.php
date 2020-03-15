<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Exception\DomainException;
use Innmind\Url\Path;
use Innmind\Immutable\Str;

final class Name
{
    private string $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^[a-zA-Z0-9_.]+$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function asPath(): Path
    {
        return Path::of(
            Str::of($this->value)
                ->replace('.', '/')
                ->prepend('/')
                ->append('/')
                ->toString(),
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
