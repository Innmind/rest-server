<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\RequestListener;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Negotiation\Negotiator;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $f;
    protected $k;
    protected $r;

    public function setUp()
    {
        $this->l = new RequestListener(
            $this->r = new Registry,
            new Parser(
                new Serializer(
                    [
                        new ResourceNormalizer(
                            new ResourceBuilder(
                                PropertyAccess::createPropertyAccessor(),
                                new EventDispatcher
                            )
                        ),
                    ],
                    [new JsonEncoder]
                ),
                $this->f = new Formats,
                new Negotiator
            )
        );
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::REQUEST => [
                    ['determineFormat', -10],
                    ['computeDefinition', 20],
                ],
            ],
            RequestListener::getSubscribedEvents()
        );
    }

    public function testComputeDefinition()
    {
        $r = new Request;
        $e = new GetResponseEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST
        );

        $this->assertSame(null, $this->l->computeDefinition($e));
        $this->assertSame([], $r->attributes->keys());

        $r->attributes->set(RouteKeys::DEFINITION, $res = new Resource('foo'));

        $this->assertSame(null, $this->l->computeDefinition($e));
        $this->assertSame($res, $r->attributes->get(RouteKeys::DEFINITION));

        $coll = new Collection('bar');
        $res->setStorage('foo');
        $coll->addResource($res);
        $this->r->addCollection($coll);
        $r->attributes->set(RouteKeys::DEFINITION, 'bar::foo');

        $this->assertSame(null, $this->l->computeDefinition($e));
        $this->assertSame($res, $r->attributes->get(RouteKeys::DEFINITION));
    }

    public function testDetermineFormat()
    {
        $this->f->add('json', 'application/json', 1);
        $r = new Request;
        $e = new GetResponseEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST
        );

        $this->assertSame(null, $this->l->determineFormat($e));
        $this->assertSame('html', $r->getRequestFormat());

        $r->attributes->set(RouteKeys::DEFINITION, new Resource('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'create');
        $r->headers->set('Content-Type', 'application/json');
        $r->headers->set('Accept', 'application/json');

        $this->assertSame(null, $this->l->determineFormat($e));
        $this->assertSame('json', $r->getRequestFormat());
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function testThrowWhenContentTypeNotSupported()
    {
        $r = new Request;
        $e = new GetResponseEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $r->attributes->set(RouteKeys::DEFINITION, new Resource('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'create');
        $r->headers->set('Content-Type', 'application/json');

        $this->l->determineFormat($e);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException
     */
    public function testThrowWhenAcceptContentNotSupported()
    {
        $this->f->add('json', 'application/json', 1);
        $r = new Request;
        $e = new GetResponseEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $r->attributes->set(RouteKeys::DEFINITION, new Resource('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'create');
        $r->headers->set('Content-Type', 'application/json');
        $r->headers->set('Accept', 'text/html');

        $this->l->determineFormat($e);
    }
}
