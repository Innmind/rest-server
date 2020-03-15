<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Exception\LogicException;
use Innmind\Url\Path;
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

    public function outOf(Path $path): Path
    {
        if ($this->value->empty()) {
            return $path;
        }

        $path = Str::of($path->toString());
        $prefix = $path->substring(0, $this->value->length());

        if (!$prefix->equals($this->value)) {
            throw new LogicException($path->toString());
        }

        return Path::of(
            $path->substring($this->value->length())->toString(),
        );
    }

    public function __toString(): string
    {
        return $this->value->toString();
    }
}
