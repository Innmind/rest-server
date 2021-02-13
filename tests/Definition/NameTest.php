<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Definition\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class NameTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll($this->value())
            ->then(function(string $string): void {
                $this->assertSame($string, (new Name($string))->toString());
            });
    }

    public function testThrowWhenInvalidName()
    {
        $this
            ->forAll(Set\Unicode::strings()->filter(static function(string $string): bool {
                return !\preg_match('~^[a-zA-Z0-9_.]$~', $string);
            }))
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

    private function value(): Set
    {
        return Set\Decorate::immutable(
            static fn(array $chars) => \implode('', $chars),
            Set\Sequence::of(
                Set\Decorate::immutable(
                    static fn($ord) => \chr($ord),
                    new Set\Either(
                        Set\Integers::between(48, 57), // 0-9
                        Set\Integers::between(65, 90), // A-Z
                        Set\Integers::between(97, 122), // a-z
                        Set\Elements::of(46, 95), // ., _
                    ),
                ),
                Set\Integers::between(1, 50),
            ),
        );
    }
}
