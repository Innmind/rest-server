<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\RouterListener;
use Innmind\Rest\Server\Routing\RouteKeys;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RouterLitenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $rs;

    public function setUp()
    {
        $this->l = new RouterListener(
            $router = $this
                ->getMockBuilder(Router::class)
                ->disableOriginalConstructor()
                ->setMethods(['matchRequest', 'getContext'])
                ->getMock(),
            $this->rs = new RequestStack
        );
        $router
            ->method('matchRequest')
            ->willReturn([
                RouteKeys::DEFINITION => 'foo::bar',
                RouteKeys::ACTION => 'index',
            ]);
        $context = $this
            ->getMockBuilder(RequestContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $router
            ->method('getContext')
            ->willReturn($context);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::REQUEST => [['matchRoute', 32]],
                KernelEvents::FINISH_REQUEST => 'updateStack',
            ],
            RouterListener::getSubscribedEvents()
        );
    }

    public function testMatchRoute()
    {
        $request = new Request(['foo' => 'bar']);
        $event = $this
            ->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->method('getRequest')
            ->willReturn($request);
        $this->assertSame(null, $this->rs->getCurrentRequest());
        $this->assertSame(null, $this->l->matchRoute($event));
        $this->assertSame($request, $this->rs->getCurrentRequest());
        $this->assertSame(
            [
                RouteKeys::DEFINITION => 'foo::bar',
                RouteKeys::ACTION => 'index'
            ],
            $request->attributes->all()
        );
    }

    public function testUpdateStack()
    {
        $this->rs->push(new Request);
        $this->l->updateStack();
        $this->assertSame(null, $this->rs->getCurrentRequest());
    }
}
