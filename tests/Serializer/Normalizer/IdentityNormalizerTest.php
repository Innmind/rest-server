<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\IdentityNormalizer,
    Identity
};
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdentityNormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            NormalizerInterface::class,
            new IdentityNormalizer
        );
    }

    public function testSupportsNormalization()
    {
        $normalizer = new IdentityNormalizer;

        $this->assertTrue($normalizer->supportsNormalization(new Identity(24)));
        $this->assertFalse($normalizer->supportsNormalization(new \stdClass));
    }

    public function testNormalize()
    {
        $normalizer = new IdentityNormalizer;

        $this->assertSame(
            ['identity' => '24'],
            $normalizer->normalize(
                new Identity('24')
            )
        );
    }
}
