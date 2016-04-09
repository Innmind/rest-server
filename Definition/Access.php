<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Immutable\SetInterface;

class Access
{
    const READ = 'READ';
    const CREATE = 'CREATE';
    const UPDATE = 'UPDATE';

    private $mask;

    public function __construct(SetInterface $mask)
    {
        if ((string) $mask->type() !== 'string') {
            throw new InvalidArgumentException;
        }

        $this->mask = $mask;
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
}
