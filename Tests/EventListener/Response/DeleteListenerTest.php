<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\DeleteListener;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Routing\RouteKeys;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class DeleteListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $k;

    public function setUp()
    {
        $this->l = new DeleteListener;
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testBuildResponse()
    {
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $r = new Request,
            HttpKernel::MASTER_REQUEST,
            null
        );
        $r->attributes->set(RouteKeys::DEFINITION, new Definition('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'delete');

        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertTrue($event->hasResponse());
        $this->assertSame(
            204,
            $event->getResponse()->getStatusCode()
        );
    }

    public function testDoesntBuildResponse()
    {
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $r = new Request,
            HttpKernel::MASTER_REQUEST,
            null
        );
        $r->attributes->set(RouteKeys::DEFINITION, new Definition('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'get');

        $this->l->buildResponse($event);
        $this->assertFalse($event->hasResponse());
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::VIEW => [['buildResponse', 10]]],
            DeleteListener::getSubscribedEvents()
        );
    }
}
