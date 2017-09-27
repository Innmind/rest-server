<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\DelegationVerifier,
    Request\Verifier\Verifier,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class DelegationVerifierTest extends TestCase
{
    public function testInterface()
    {
        $verifier = new DelegationVerifier(
            new Map('int', Verifier::class)
        );

        $this->assertInstanceOf(Verifier::class, $verifier);
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<int, Innmind\Rest\Server\Request\Verifier\Verifier>
     */
    public function testThrowWhenInvalidMapOfVerifiers()
    {
        new DelegationVerifier(new Map('int', 'object'));
    }

    public function testVerify()
    {
        $verifier = new DelegationVerifier(
            (new Map('int', Verifier::class))
                ->put(
                    100,
                    $verifier1 = $this->createMock(Verifier::class)
                )
                ->put(
                    20,
                    $verifier2 = $this->createMock(Verifier::class)
                )
        );
        $count = 0;
        $verifier1
            ->method('verify')
            ->will($this->returnCallback(function() use (&$count) {
                $this->assertSame(1, ++$count);
            }));
        $verifier2
            ->method('verify')
            ->will($this->returnCallback(function() use (&$count) {
                $this->assertSame(2, ++$count);
            }));

        $this->assertSame(
            null,
            $verifier->verify(
                $this->createMock(ServerRequest::class),
                new HttpResource(
                    'foo',
                    new Identity('uuid'),
                    new Map('string', Property::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('command'),
                    true,
                    new Map('string', 'string')
                )
            )
        );
        $this->assertSame(2, $count);
    }

    /**
     * @expectedException Exception
     */
    public function testThrowWhenSubVerifierThrows()
    {
        $verifier = new DelegationVerifier(
            (new Map('int', Verifier::class))
                ->put(
                    1,
                    $verifier1 = $this->createMock(Verifier::class)
                )
        );
        $verifier1
            ->method('verify')
            ->will($this->returnCallback(function() use (&$count) {
                throw new \Exception;
            }));

        $verifier->verify(
            $this->createMock(ServerRequest::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            )
        );
    }
}
