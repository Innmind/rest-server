<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Link,
    Reference,
    Identity,
    Link\Parameter\Parameter,
};
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    public function testInterface()
    {
        $directory = require 'fixtures/mapping.php';

        $link = new Link(
            $reference = new Reference(
                $directory->definition('image'),
                $this->createMock(Identity::class)
            ),
            'rel',
            $parameter = new Parameter('foo', 'bar')
        );

        $this->assertSame($reference, $link->reference());
        $this->assertSame('rel', $link->relationship());
        $this->assertTrue($link->has('foo'));
        $this->assertSame($parameter, $link->get('foo'));
        $this->assertFalse($link->has('bar'));
    }
}
