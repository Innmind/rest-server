<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Immutable\{
    SetInterface,
    Set,
    StringPrimitive as Str
};

final class Action
{
    const LIST = 'list';
    const GET = 'get';
    const CREATE = 'create';
    const UPDATE = 'update';
    const REMOVE = 'remove';
    const LINK = 'link';
    const UNLINK = 'unlink';
    const OPTIONS = 'options';

    private $action;

    public function __construct(string $action)
    {
        $const = (new Str($action))
            ->toUpper()
            ->prepend('self::');

        if (!defined((string) $const)) {
            throw new InvalidArgumentException;
        }

        $this->action = constant((string) $const);
    }

    public function equals(self $action): bool
    {
        return $this->action === (string) $action;
    }

    public function __toString(): string
    {
        return $this->action;
    }

    /**
     * @return SetInterface<string>
     */
    public static function all(): SetInterface
    {
        return (new Set('string'))
            ->add(self::LIST)
            ->add(self::GET)
            ->add(self::CREATE)
            ->add(self::UPDATE)
            ->add(self::REMOVE)
            ->add(self::LINK)
            ->add(self::UNLINK)
            ->add(self::OPTIONS);
    }
}
