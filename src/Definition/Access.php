<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    Set,
    SetInterface,
};

final class Access
{
    public const READ = 'READ';
    public const CREATE = 'CREATE';
    public const UPDATE = 'UPDATE';

    private Set $mask;

    public function __construct(string $first, string ...$mask)
    {
        $this->mask = Set::of('string', $first, ...$mask);
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
     * @return SetInterface<string>
     */
    public function mask(): SetInterface
    {
        return $this->mask;
    }

    public function matches(self $mask): bool
    {
        foreach ($mask->mask() as $access) {
            if (!$this->mask->contains($access)) {
                return false;
            }
        }

        return true;
    }
}
