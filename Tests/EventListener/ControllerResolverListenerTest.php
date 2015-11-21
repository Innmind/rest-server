<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\ControllerResolverListener;
use Innmind\Rest\Server\Controller\ResourceController;
use Innmind\Rest\Server\Routing\RouteKeys;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

class ControllerResolverListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;

    public function setUp()
    {
        $this->l = new ControllerResolverListener(
            $this
                ->getMockBuilder(ResourceController::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::REQUEST => 'injectController'],
            ControllerResolverListener::getSubscribedEvents()
        );
    }

    public function testInjectController()
    {
        $r = new Request;
        $e = new GetResponseEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $r->attributes->set(RouteKeys::ACTION, 'index');

        $this->assertSame(null, $this->l->injectController($e));
        $this->assertTrue($r->attributes->has('_controller'));
        $controller = $r->attributes->get('_controller');
        $this->assertTrue(is_callable($controller));
        $this->assertInstanceOf(ResourceController::class, $controller[0]);
        $this->assertSame('indexAction', $controller[1]);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage No action found
     */
    public function testThrowIfNoActionFound()
    {
        $e = new GetResponseEvent(
            $this->k,
            new Request,
            HttpKernel::MASTER_REQUEST
        );

        $this->l->injectController($e);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid action "foo"
     */
    public function testThrowIfInvalidAction()
    {
        $r = new Request;
        $e = new GetResponseEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $r->attributes->set(RouteKeys::ACTION, 'foo');

        $this->l->injectController($e);
    }
}
