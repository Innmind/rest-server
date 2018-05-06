<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Routing\Name,
    Exception\DomainException,
};
use Innmind\Url\PathInterface;
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
                $this->assertSame($string, (string) new Name($string));
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

    public function testAsPath()
    {
        $name = new Name('foo.bar');

        $path = $name->asPath();

        $this->assertInstanceOf(PathInterface::class, $path);
        $this->assertSame('/foo/bar/', (string) $path);
    }
}
