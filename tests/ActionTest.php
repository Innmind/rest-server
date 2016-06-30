<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\Action;
use Innmind\Immutable\SetInterface;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $action = new Action(Action::LIST);

        $this->assertSame(Action::LIST, (string) $action);
        $this->assertTrue($action->equals(new Action(Action::LIST)));
        $this->assertFalse($action->equals(new Action(Action::GET)));
        $all = Action::all();
        $this->assertInstanceOf(SetInterface::class, $all);
        $this->assertSame('string', (string) $all->type());
        $this->assertSame(7, $all->size());
        $this->assertSame(
            ['list', 'get', 'create', 'update', 'remove', 'link', 'unlink'],
            $all->toPrimitive()
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidAction()
    {
        new Action('foo');
    }
}
