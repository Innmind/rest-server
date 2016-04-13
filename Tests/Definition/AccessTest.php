<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition;

use Innmind\Rest\Server\Definition\Access;
use Innmind\Immutable\Set;

class AccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenGivingInvalidMask()
    {
        new Access(new Set('int'));
    }

    public function testIsReadable()
    {
        $this->assertTrue(
            (new Access(
                (new Set('string'))
                    ->add(Access::READ)
            ))
                ->isReadable()
        );
        $this->assertTrue(
            (new Access(
                (new Set('string'))
                    ->add(Access::READ)
                    ->add(Access::CREATE)
                    ->add(Access::UPDATE)
            ))
                ->isReadable()
        );
    }

    public function testIsCreatable()
    {
        $this->assertTrue(
            (new Access(
                (new Set('string'))
                    ->add(Access::CREATE)
            ))
                ->isCreatable()
        );
        $this->assertTrue(
            (new Access(
                (new Set('string'))
                    ->add(Access::READ)
                    ->add(Access::CREATE)
                    ->add(Access::UPDATE)
            ))
                ->isCreatable()
        );
    }

    public function testIsUpdatable()
    {
        $this->assertTrue(
            (new Access(
                (new Set('string'))
                    ->add(Access::UPDATE)
            ))
                ->isUpdatable()
        );
        $this->assertTrue(
            (new Access(
                (new Set('string'))
                    ->add(Access::READ)
                    ->add(Access::CREATE)
                    ->add(Access::UPDATE)
            ))
                ->isUpdatable()
        );
    }

    public function testMask()
    {
        $a = (new Access(
            $m = (new Set('string'))
                ->add(Access::READ)
                ->add(Access::CREATE)
                ->add(Access::UPDATE)
        ));

        $this->assertSame($m, $a->mask());
    }

    public function testMatches()
    {
        $a = (new Access(
            (new Set('string'))
                ->add(Access::READ)
                ->add(Access::CREATE)
                ->add(Access::UPDATE)
        ));
        $this->assertTrue($a->matches(
            new Access(
                (new Set('string'))->add(Access::READ)
            )
        ));
        $this->assertTrue($a->matches(
            new Access(
                (new Set('string'))->add(Access::CREATE)
            )
        ));
        $this->assertTrue($a->matches(
            new Access(
                (new Set('string'))->add(Access::UPDATE)
            )
        ));

        $a = (new Access(
            $m = (new Set('string'))
                ->add(Access::READ)
        ));
        $this->assertTrue($a->matches(
            new Access(
                (new Set('string'))->add(Access::READ)
            )
        ));
        $this->assertFalse($a->matches(
            new Access(
                (new Set('string'))->add(Access::CREATE)
            )
        ));
        $this->assertFalse($a->matches(
            new Access(
                (new Set('string'))->add(Access::UPDATE)
            )
        ));
    }
}
