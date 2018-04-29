<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    Sequence,
    Set,
    SetInterface
};

final class Access
{
    public const READ = 'READ';
    public const CREATE = 'CREATE';
    public const UPDATE = 'UPDATE';

    private $mask;

    public function __construct(string ...$mask)
    {
        $this->mask = (new Sequence(...$mask))->reduce(
            new Set('string'),
            static function (Set $carry, string $element): Set {
                return $carry->add($element);
            }
        );
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
