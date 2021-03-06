<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;

final class Access
{
    public const READ = 'READ';
    public const CREATE = 'CREATE';
    public const UPDATE = 'UPDATE';

    /** @var Set<string> */
    private Set $mask;

    public function __construct(string $first, string ...$mask)
    {
        $this->mask = Set::strings($first, ...$mask);
    }

    public function isReadable(): bool
    {
        return $this->mask->contains(self::READ);
    }

    public function isCreatable(): bool
    {
        return $this->mask->contains(self::CREATE);
    }

    public function isUpdatable(): bool
    {
        return $this->mask->contains(self::UPDATE);
    }

    /**
     * @return Set<string>
     */
    public function mask(): Set
    {
        return $this->mask;
    }

    public function matches(self $mask): bool
    {
        foreach (unwrap($mask->mask()) as $access) {
            if (!$this->mask->contains($access)) {
                return false;
            }
        }

        return true;
    }
}
