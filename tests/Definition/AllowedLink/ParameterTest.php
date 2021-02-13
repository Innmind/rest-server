<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\AllowedLink;

use Innmind\Rest\Server\{
    Definition\AllowedLink\Parameter,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ParameterTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(Set\Strings::any()->filter(static fn($value) => $value !== ''))
            ->then(function(string $value): void {
                $this->assertSame($value, (new Parameter($value))->name());
            });
    }

    public function testThrowOnEmptyParameter()
    {
        $this->expectException(DomainException::class);

        new Parameter('');
    }
}
