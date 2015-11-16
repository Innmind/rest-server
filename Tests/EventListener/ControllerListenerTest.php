<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\ControllerListener;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Validator;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Exception\ValidationException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $k;
    protected $i;

    public function setUp()
    {
        $this->l = new ControllerListener(
            $v = $this
                ->getMockBuilder(Validator::class)
                ->disableOriginalConstructor()
                ->getMock(),
            $p = $this
                ->getMockBuilder(Parser::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->i = $this
            ->getMockBuilder(Resource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $p
            ->method('getData')
            ->will($this->returnCallback(function($request) {
                if ($request->attributes->has('_multiple')) {
                    return new Collection;
                }

                if ($request->attributes->has('_fail')) {
                    return $this->i;
                }
            }));
        $v
            ->method('validate')
            ->will($this->returnCallback(function($r) {
                $v = $this->getMock(ConstraintViolationListInterface::class);

                if ($r === $this->i) {
                    $c = 1;
                } else {
                    $c = 0;
                }

                $v
                    ->method('count')
                    ->willReturn($c);

                return $v;
            }));
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::CONTROLLER => 'decodeRequest',
                KernelEvents::VIEW => [['validateControllerResult', 20]],
            ],
            ControllerListener::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider types
     */
    public function testDecodeRequest($action, $key)
    {
        $r= new Request;
        $e = new FilterControllerEvent(
            $this->k,
            function() {},
            $r,
            HttpKernel::MASTER_REQUEST
        );

        $this->assertSame(null, $this->l->decodeRequest($e));

        $r->attributes->set(RouteKeys::ACTION, $action);
        $r->attributes->set(RouteKeys::DEFINITION, new ResourceDefinition('foo'));

        $this->assertSame(null, $this->l->decodeRequest($e));
        $this->assertTrue($r->attributes->has($key));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\PayloadException
     * @expectedExceptionMessage You can only update one resource at a time
     */
    public function testThrowIfUpdatingMultipleResources()
    {
        $r= new Request;
        $e = new FilterControllerEvent(
            $this->k,
            function() {},
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $r->attributes->set('_multiple', true);
        $r->attributes->set(RouteKeys::ACTION, 'update');
        $r->attributes->set(RouteKeys::DEFINITION, new ResourceDefinition('foo'));

        $this->l->decodeRequest($e);
    }

    /**
     * @dataProvider types
     */
    public function testThrowWhenFailsValidation($action, $key, $access)
    {
        if ($access === null) {
            return;
        }

        try {
            $r= new Request;
            $e = new FilterControllerEvent(
                $this->k,
                function() {},
                $r,
                HttpKernel::MASTER_REQUEST
            );
            $r->attributes->set('_fail', true);
            $r->attributes->set(RouteKeys::ACTION, $action);
            $r->attributes->set(RouteKeys::DEFINITION, new ResourceDefinition('foo'));

            $this->l->decodeRequest($e);

            $this->fail('It should throw a validation exception');
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertSame($e->getAccess(), $access);
        }
    }

    /**
     * @dataProvider types
     */
    public function testValidateResult($action)
    {
        $r= new Request;
        $e = new GetResponseForControllerResultEvent(
            $this->k,
            $r,
            HttpKernel::MASTER_REQUEST,
            new Resource
        );

        $this->assertSame(null, $this->l->validateControllerResult($e));

        $r->attributes->set(RouteKeys::ACTION, $action);
        $r->attributes->set(RouteKeys::DEFINITION, new ResourceDefinition('foo'));

        $this->assertSame(null, $this->l->validateControllerResult($e));
    }

    /**
     * @dataProvider types
     */
    public function testThrowWhenInvalidControllerResult($action, $key, $access, $validate)
    {
        try {
            $r= new Request;
            $e = new GetResponseForControllerResultEvent(
                $this->k,
                $r,
                HttpKernel::MASTER_REQUEST,
                $this->i
            );

            $this->assertSame(null, $this->l->validateControllerResult($e));

            $r->attributes->set(RouteKeys::ACTION, $action);
            $r->attributes->set(RouteKeys::DEFINITION, new ResourceDefinition('foo'));
            $r->attributes->set('_fail', true);

            $this->l->validateControllerResult($e);

            if ($validate === true) {
                $this->fail('It should throw a validation exception');
            }
        } catch (\Exception $e) {
            if ($validate === true) {
                $this->assertInstanceOf(ValidationException::class, $e);
                $this->assertSame('READ', $e->getAccess());
            } else {
                $this->fail('It should not throw an exception');
            }
        }
    }

    public function types()
    {
        return [
            ['index', 'definition', null, true],
            ['get', 'definition', null, true],
            ['delete', 'definition', null, false],
            ['options', 'definition', null, false],
            ['create', 'resources', 'CREATE', true],
            ['update', 'resource', 'UPDATE', true],
        ];
    }
}
