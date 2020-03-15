<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\Action;
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{
    public function testInterface()
    {
        $action = Action::list();

        $this->assertSame('list', (string) $action);
        $this->assertTrue($action->equals(Action::list()));
        $this->assertFalse($action->equals(Action::get()));
        $this->assertSame(Action::list(), Action::list());
        $all = Action::all();
        $this->assertInstanceOf(Set::class, $all);
        $this->assertSame(Action::class, (string) $all->type());
        $this->assertSame(8, $all->size());
        $this->assertSame(
            [
                Action::list(),
                Action::get(),
                Action::create(),
                Action::update(),
                Action::remove(),
                Action::link(),
                Action::unlink(),
                Action::options(),
            ],
            unwrap($all),
        );
    }
}
