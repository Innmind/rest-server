<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\IdentitiesNormalizer,
    Identity as IdentityInterface,
    Identity\Identity,
};
use Innmind\Immutable\Set;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\TestCase;

class IdentitiesNormalizerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            NormalizerInterface::class,
            new IdentitiesNormalizer
        );
    }

    public function testSupportsNormalization()
    {
        $normalizer = new IdentitiesNormalizer;

        $this->assertTrue($normalizer->supportsNormalization(new Set(IdentityInterface::class)));
        $this->assertFalse($normalizer->supportsNormalization(new Set(Identity::class)));
        $this->assertFalse($normalizer->supportsNormalization(new Set('object')));
    }

    public function testNormalize()
    {
        $normalizer = new IdentitiesNormalizer;

        $this->assertSame(
            ['identities' => [42, '24']],
            $normalizer->normalize(
                (new Set(IdentityInterface::class))
                    ->add(new Identity(42))
                    ->add(new Identity('24'))
            )
        );
    }
}
