<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\DelegationVerifier,
    Request\Verifier\Verifier,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class DelegationVerifierTest extends TestCase
{
    public function testInterface()
    {
        $verifier = new DelegationVerifier;

        $this->assertInstanceOf(Verifier::class, $verifier);
    }

    public function testVerify()
    {
        $verify = new DelegationVerifier(
            $verifier1 = $this->createMock(Verifier::class),
            $verifier2 = $this->createMock(Verifier::class)
        );
        $count = 0;
        $verifier1
            ->method('__invoke')
            ->will($this->returnCallback(function() use (&$count) {
                $this->assertSame(1, ++$count);
            }));
        $verifier2
            ->method('__invoke')
            ->will($this->returnCallback(function() use (&$count) {
                $this->assertSame(2, ++$count);
            }));

        $this->assertNull(
            $verify(
                $this->createMock(ServerRequest::class),
                HttpResource::rangeable(
                    'foo',
                    new Gateway('command'),
                    new Identity('uuid'),
                    Set::of(Property::class)
                )
            )
        );
        $this->assertSame(2, $count);
    }

    public function testThrowWhenSubVerifierThrows()
    {
        $verify = new DelegationVerifier(
            $verifier1 = $this->createMock(Verifier::class)
        );
        $verifier1
            ->method('__invoke')
            ->will($this->returnCallback(static function() use (&$count) {
                throw new \Exception;
            }));

        $this->expectException(\Exception::class);

        $verify(
            $this->createMock(ServerRequest::class),
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        );
    }
}
