<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Exception\DomainException;
use Innmind\Url\{
    PathInterface,
    Path,
};
use Innmind\Immutable\Str;

final class Name
{
    private $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^[a-zA-Z0-9_.]+$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function asPath(): PathInterface
    {
        return new Path(
            (string) Str::of($this->value)
                ->replace('.', '/')
                ->prepend('/')
                ->append('/')
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
