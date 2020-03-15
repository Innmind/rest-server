<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Immutable\Set;

final class Action
{
    private const LIST = 'list';
    private const GET = 'get';
    private const CREATE = 'create';
    private const UPDATE = 'update';
    private const REMOVE = 'remove';
    private const LINK = 'link';
    private const UNLINK = 'unlink';
    private const OPTIONS = 'options';

    private static ?self $list = null;
    private static ?self $get = null;
    private static ?self $create = null;
    private static ?self $update = null;
    private static ?self $remove = null;
    private static ?self $link = null;
    private static ?self $unlink = null;
    private static ?self $options = null;

    private string $action;

    private function __construct(string $action)
    {
        $this->action = $action;
    }

    public static function list(): self
    {
        return self::$list ??= new self(self::LIST);
    }

    public static function get(): self
    {
        return self::$get ??= new self(self::GET);
    }

    public static function create(): self
    {
        return self::$create ??= new self(self::CREATE);
    }

    public static function update(): self
    {
        return self::$update ??= new self(self::UPDATE);
    }

    public static function remove(): self
    {
        return self::$remove ??= new self(self::REMOVE);
    }

    public static function link(): self
    {
        return self::$link ??= new self(self::LINK);
    }

    public static function unlink(): self
    {
        return self::$unlink ??= new self(self::UNLINK);
    }

    public static function options(): self
    {
        return self::$options ??= new self(self::OPTIONS);
    }

    public function equals(self $action): bool
    {
        return $this->action === $action->toString();
    }

    public function toString(): string
    {
        return $this->action;
    }

    /**
     * @return Set<self>
     */
    public static function all(): Set
    {
        return Set::of(
            self::class,
            self::list(),
            self::get(),
            self::create(),
            self::update(),
            self::remove(),
            self::link(),
            self::unlink(),
            self::options()
        );
    }
}
