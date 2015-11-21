<?php

namespace Innmind\Rest\Server\Tests\Request;

use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Serializer\Encoder\FormEncoder;
use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Negotiation\Negotiator;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    protected $p;

    public function setUp()
    {
        $serializer = new Serializer(
            [new ResourceNormalizer(new ResourceBuilder(
                PropertyAccess::createPropertyAccessor(),
                new EventDispatcher
            ))],
            [new FormEncoder, new JsonEncoder]
        );
        $formats = new Formats;
        $formats
            ->add('json', 'application/json', 10)
            ->add('form', 'application/x-www-form-urlencoded', 1);

        $this->p = new Parser(
            $serializer,
            $formats,
            new Negotiator
        );
    }

    public function testIsContentTypeAcceptable()
    {
        $r = new HttpRequest;
        $r->headers->add(['Content-Type' => 'application/json']);

        $this->assertTrue($this->p->isContentTypeAcceptable($r));
    }

    public function testIsContentTypeNotAcceptable()
    {
        $r = new HttpRequest;
        $r->headers->add(['Content-Type' => 'text/json']);

        $this->assertFalse($this->p->isContentTypeAcceptable($r));
    }

    public function testIsRequestedTypeAcceptable()
    {
        $r = new HttpRequest;
        $r->headers->add(['Accept' => 'application/json']);

        $this->assertTrue($this->p->isRequestedTypeAcceptable($r));
    }

    public function testIsRequestedTypeNotAcceptable()
    {
        $r = new HttpRequest;
        $r->headers->add(['Accept' => 'text/json']);

        $this->assertFalse($this->p->isRequestedTypeAcceptable($r));
    }

    public function testGetData()
    {
        $data = [
            'foo' => 'bar',
            'sub' => [
                'foo' => 'bar',
            ],
            'subColl' => [[
                'foo' => 'bar',
            ]],
        ];
        $subDef = (new ResourceDefinition('sub'))
            ->addProperty(
                (new Property('foo'))
                    ->setType('string')
            );
        $definition = new ResourceDefinition('foo');
        $definition
            ->addProperty(
                (new Property('foo'))
                    ->setType('string')
            )
            ->addProperty(
                (new Property('sub'))
                    ->setType('resource')
                    ->addOption('resource', $subDef)
            )
            ->addProperty(
                (new Property('subColl'))
                    ->setType('array')
                    ->addOption('inner_type', 'resource')
                    ->addOption('resource', $subDef)
            );
        $expectedColl = new Collection;
        $expectedColl[] = (new HttpResource)
            ->setDefinition($subDef)
            ->set('foo', 'bar');
        $expected = new HttpResource;
        $expected
            ->setDefinition($definition)
            ->set('foo', 'bar')
            ->set(
                'sub',
                (new HttpResource)
                    ->setDefinition($subDef)
                    ->set('foo', 'bar')
            )
            ->set('subColl', $expectedColl);
        $r = new HttpRequest(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode(['resource' => $data])
        );
        $r->headers->add(['Content-Type' => 'application/json']);

        $this->assertEquals(
            $expected,
            $this->p->getData($r, $definition)
        );
    }

    public function testDoesntThrowIfPayloadDoesntMatchDefinition()
    {
        $definition = new ResourceDefinition('foo');
        $definition
            ->addProperty(
                (new Property('foo'))
                    ->setType('int')
            )
            ->addProperty(
                (new Property('bar'))
                    ->setType('string')
            );
        $r = new HttpRequest(
            [],
            [],
            [],
            [],
            [],
            [],
            '{"resource":{"foo":"42"}}'
        );
        $r->headers->add(['Content-Type' => 'application/json']);

        try {
            $this->p->getData($r, $definition);
        } catch (\Exception $e) {
            $this->fail('An exception has been raised while building invalid data');
        }
    }

    public function testGetCollectionOfData()
    {
        $def = new ResourceDefinition('foo');
        $def->addProperty(
            (new Property('foo'))
                ->setType('string')
        );
        $data = [
            ['foo' => 'bar'],
            ['foo' => 'baz'],
        ];
        $r = new HttpRequest(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode(['resources' => $data])
        );
        $r->headers->add(['Content-Type' => 'application/json']);
        $expected = new Collection;
        $o = new HttpResource;
        $o
            ->setDefinition($def)
            ->set('foo', 'bar');
        $expected[] = $o;
        $o = new HttpResource;
        $o
            ->setDefinition($def)
            ->set('foo', 'baz');
        $expected[] = $o;

        $this->assertEquals(
            $expected,
            $this->p->getData($r, $def)
        );
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\UnsupportedException
     * @expectedExceptionMessage Data must be set under the key "resource" or "resources"
     */
    public function testThrowIfNoResourceKeyInPayload()
    {
        $definition = new ResourceDefinition('foo');
        $definition->addProperty(
            (new Property('foo'))
                ->setType('int')
        );
        $r = new HttpRequest(
            [],
            [],
            [],
            [],
            [],
            [],
            '{"foo":42}'
        );
        $r->headers->add(['Content-Type' => 'application/json']);

        $this->p->getData($r, $definition);
    }

    public function testGetRequestedFormat()
    {
        $r = new HttpRequest;
        $r->headers->add(['Accept' => 'application/json']);

        $this->assertSame(
            'json',
            $this->p->getRequestedFormat($r)
        );

        $r = new HttpRequest;
        $r->headers->add(['Accept' => '*/*']);

        $this->assertSame(
            'json',
            $this->p->getRequestedFormat($r)
        );

        $r = new HttpRequest;
        $r->headers->add(['Accept' => 'application/x-www-form-urlencoded, */*; q=0.8']);

        $this->assertSame(
            'form',
            $this->p->getRequestedFormat($r)
        );
    }
}
