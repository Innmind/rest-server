<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Immutable\{
    SetInterface,
    Set,
};

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

    private static $list;
    private static $get;
    private static $create;
    private static $update;
    private static $remove;
    private static $link;
    private static $unlink;
    private static $options;

    private $action;

    private function __construct(string $action)
    {
        $this->action = $action;
    }

    public static function list(): self
    {
        return self::$list ?? self::$list = new self(self::LIST);
    }

    public static function get(): self
    {
        return self::$get ?? self::$get = new self(self::GET);
    }

    public static function create(): self
    {
        return self::$create ?? self::$create = new self(self::CREATE);
    }

    public static function update(): self
    {
        return self::$update ?? self::$update = new self(self::UPDATE);
    }

    public static function remove(): self
    {
        return self::$remove ?? self::$remove = new self(self::REMOVE);
    }

    public static function link(): self
    {
        return self::$link ?? self::$link = new self(self::LINK);
    }

    public static function unlink(): self
    {
        return self::$unlink ?? self::$unlink = new self(self::UNLINK);
    }

    public static function options(): self
    {
        return self::$options ?? self::$options = new self(self::OPTIONS);
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
     * @return SetInterface<self>
     */
    public static function all(): SetInterface
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
