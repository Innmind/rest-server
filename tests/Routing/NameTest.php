<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Routing\Name,
    Exception\DomainException,
};
use Innmind\Url\Path;
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
                return !preg_match('~^[a-zA-Z0-9_.]$~', $string);
            }))
            ->then(function(string $string): void {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Name($string);
            });
    }

    public function testAsPath()
    {
        $name = new Name('foo.bar');

        $path = $name->asPath();

        $this->assertInstanceOf(Path::class, $path);
        $this->assertSame('/foo/bar/', $path->toString());
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
