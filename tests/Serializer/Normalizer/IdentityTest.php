<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\Identity as Normalizer,
    Identity\Identity,
};
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    public function testNormalize()
    {
        $normalize = new Normalizer;

        $this->assertSame(
            ['identity' => '24'],
            $normalize(
                new Identity('24')
            )
        );
    }
}
