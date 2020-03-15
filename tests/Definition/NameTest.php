<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Definition\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class NameTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->minimumEvaluationRatio(0.01)
            ->forAll(Generator\string())
            ->when(static function(string $string): bool {
                return (bool) preg_match('~^[a-zA-Z0-9_.]$~', $string);
            })
            ->then(function(string $string): void {
                $this->assertSame($string, (new Name($string))->toString());
            });
    }

    public function testThrowWhenInvalidName()
    {
        $this
            ->forAll(Generator\string())
            ->when(static function(string $string): bool {
                return !preg_match('~^[a-zA-Z0-9_.]$~', $string);
            })
            ->then(function(string $string): void {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Name($string);
            });
    }

    public function testUnder()
    {
        $name = new Name('foo');
        $directory = new Name('bar');

        $joined = $name->under($directory);

        $this->assertInstanceOf(Name::class, $joined);
        $this->assertSame('foo', $name->toString());
        $this->assertSame('bar', $directory->toString());
        $this->assertSame('bar.foo', $joined->toString());
    }
}
