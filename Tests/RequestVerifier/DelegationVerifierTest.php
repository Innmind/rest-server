<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\RequestVerifier;

use Innmind\Rest\Server\{
    RequestVerifier\DelegationVerifier,
    RequestVerifier\VerifierInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\{
    Map,
    Collection
};

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
                    $verifier1 = $this->getMock(VerifierInterface::class)
                )
                ->put(
                    20,
                    $verifier2 = $this->getMock(VerifierInterface::class)
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
                $this->getMock(ServerRequestInterface::class),
                new HttpResource(
                    'foo',
                    new Identity('uuid'),
                    new Map('string', Property::class),
                    new Collection([]),
                    new Collection([]),
                    new Gateway('command'),
                    true
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
                    $verifier1 = $this->getMock(VerifierInterface::class)
                )
        );
        $verifier1
            ->method('verify')
            ->will($this->returnCallback(function() use (&$count) {
                throw new \Exception;
            }));

        $verifier->verify(
            $this->getMock(ServerRequestInterface::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                true
            )
        );
    }
}
