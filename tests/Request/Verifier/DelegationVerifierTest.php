<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\DelegationVerifier,
    Request\Verifier\VerifierInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\Map;

class DelegationVerifierTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $verifier = new DelegationVerifier(
            new Map('int', VerifierInterface::class)
        );

        $this->assertInstanceOf(VerifierInterface::class, $verifier);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidMapOfVerifiers()
    {
        new DelegationVerifier(new Map('int', 'object'));
    }

    public function testVerify()
    {
        $verifier = new DelegationVerifier(
            (new Map('int', VerifierInterface::class))
                ->put(
                    100,
                    $verifier1 = $this->createMock(VerifierInterface::class)
                )
                ->put(
                    20,
                    $verifier2 = $this->createMock(VerifierInterface::class)
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
                $this->createMock(ServerRequestInterface::class),
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
            (new Map('int', VerifierInterface::class))
                ->put(
                    1,
                    $verifier1 = $this->createMock(VerifierInterface::class)
                )
        );
        $verifier1
            ->method('verify')
            ->will($this->returnCallback(function() use (&$count) {
                throw new \Exception;
            }));

        $verifier->verify(
            $this->createMock(ServerRequestInterface::class),
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
