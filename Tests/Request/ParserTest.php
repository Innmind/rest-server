<?php

namespace Innmind\Rest\Server\Tests\Request;

use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Serializer\Encoder\FormEncoder;
use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Negotiation\Negotiator;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    protected $p;

    public function setUp()
    {
        $serializer = new Serializer(
            [new ResourceNormalizer],
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
        $expected = new \stdClass;
        $sub = new \stdClass;
        $sub->foo = 'bar';
        $expected->foo = 'bar';
        $expected->sub = $sub;
        $expected->subColl = [$sub];
        $subDef = (new Definition('sub'))
            ->addProperty(
                (new Property('foo'))
                    ->setType('string')
            );
        $definition = new Definition('foo');
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

    /**
     * @expectedException Innmind\Rest\Server\Exception\PayloadException
     * @expectedExceptionMessage Bad request payload
     */
    public function testThrowIfPayloadDoesntMatchDefinition()
    {
        $definition = new Definition('foo');
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

        $this->p->getData($r, $definition);
    }

    public function testGetCollectionOfData()
    {
        $def = new Definition('foo');
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
        $expected = [];
        $o = new \stdClass;
        $o->foo = 'bar';
        $expected[] = $o;
        $o = new \stdClass;
        $o->foo = 'baz';
        $expected[] = $o;

        $this->assertEquals(
            $expected,
            $this->p->getData($r, $def)
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\PayloadException
     * @expectedExceptionMessage The payload must be set either under the key "resource" or "resources"
     */
    public function testThrowIfNoResourceKeyInPayload()
    {
        $definition = new Definition('foo');
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
    }
}
