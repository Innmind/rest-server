<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Definition\AllowedLink,
    Definition\AllowedLink\Parameter,
    Definition\Locator,
    Reference,
    Identity,
    Link,
    Exception\DomainException,
    Exception\DefinitionNotFound,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class AllowedLinkTest extends TestCase
{
    public function testInterface()
    {
        $allowed = new AllowedLink(
            'rel',
            'path',
            $parameter = new Parameter('foo')
        );

        $this->assertSame('rel', $allowed->relationship());
        $this->assertSame('path', $allowed->resourcePath());
        $this->assertInstanceOf(Set::class, $allowed->parameters());
        $this->assertSame(Parameter::class, (string) $allowed->parameters()->type());
        $this->assertSame([$parameter], unwrap($allowed->parameters()));
    }

    public function testThrowWhenEmptyRelationship()
    {
        $this->expectException(DomainException::class);

        new AllowedLink('', 'foo');
    }

    public function testThrowWhenEmptyResourcePath()
    {
        $this->expectException(DomainException::class);

        new AllowedLink('foo', '');
    }

    public function testThrowWhenResourcePathNotFound()
    {
        $directory = require 'fixtures/mapping.php';
        $locator = new Locator($directory);
        $allowed = new AllowedLink('rel', 'unknown');

        $this->expectException(DefinitionNotFound::class);

        $allowed->accept($locator, new Link(
            new Reference(
                $directory->definition('image'),
                $this->createMock(Identity::class)
            ),
            'rel'
        ));
    }

    public function testDoesntAcceptWhenNotExpectedDefinition()
    {
        $directory = require 'fixtures/mapping.php';
        $locator = new Locator($directory);
        $allowed = new AllowedLink('rel', 'top_dir.sub_dir.res');

        $this->assertFalse($allowed->accept($locator, new Link(
            new Reference(
                $directory->definition('image'),
                $this->createMock(Identity::class)
            ),
            'rel'
        )));
    }

    public function testDoesntAcceptWhenNotExpectedType()
    {
        $directory = require 'fixtures/mapping.php';
        $locator = new Locator($directory);
        $allowed = new AllowedLink('rel', 'top_dir.image');

        $this->assertFalse($allowed->accept($locator, new Link(
            new Reference(
                $directory->definition('image'),
                $this->createMock(Identity::class)
            ),
            'foo'
        )));
    }

    public function testDoesntAcceptWhenNotEveryParameterFound()
    {
        $directory = require 'fixtures/mapping.php';
        $locator = new Locator($directory);
        $allowed = new AllowedLink(
            'rel',
            'top_dir.image',
            new Parameter('foo')
        );

        $this->assertFalse($allowed->accept($locator, new Link(
            new Reference(
                $directory->definition('image'),
                $this->createMock(Identity::class)
            ),
            'rel'
        )));
    }

    public function testAccept()
    {
        $directory = require 'fixtures/mapping.php';
        $locator = new Locator($directory);
        $allowed = new AllowedLink(
            'rel',
            'top_dir.image',
            new Parameter('foo')
        );

        $this->assertTrue($allowed->accept($locator, new Link(
            new Reference(
                $directory->definition('image'),
                $this->createMock(Identity::class)
            ),
            'rel',
            new Link\Parameter\Parameter('foo', 'bar')
        )));
    }

    public function testAcceptByDefaultWhenNoParameterExpected()
    {
        $directory = require 'fixtures/mapping.php';
        $locator = new Locator($directory);
        $allowed = new AllowedLink(
            'rel',
            'top_dir.image'
        );

        $this->assertTrue($allowed->accept($locator, new Link(
            new Reference(
                $directory->definition('image'),
                $this->createMock(Identity::class)
            ),
            'rel',
            new Link\Parameter\Parameter('foo', 'bar')
        )));
    }
}
