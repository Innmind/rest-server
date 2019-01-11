<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\AllowedLink;

use Innmind\Rest\Server\{
    Definition\AllowedLink\Parameter,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class ParameterTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->forAll(Generator\string())
            ->when(static function(string $value): bool {
                return $value !== '';
            })
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
