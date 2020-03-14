<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Exception\LogicException;
use Innmind\Url\{
    PathInterface,
    Path,
};
use Innmind\Immutable\Str;

final class Prefix
{
    private Str $value;

    public function __construct(string $value)
    {
        $this->value = Str::of($value)->rightTrim('/');
    }

    public static function none(): self
    {
        return new self('');
    }

    public function outOf(PathInterface $path): PathInterface
    {
        if ($this->value->empty()) {
            return $path;
        }

        $path = Str::of((string) $path);
        $prefix = $path->substring(0, $this->value->length());

        if (!$prefix->equals($this->value)) {
            throw new LogicException((string) $path);
        }

        return new Path(
            (string) $path->substring($this->value->length())
        );
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
