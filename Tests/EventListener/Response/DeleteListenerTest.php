<?php

namespace Innmind\Rest\Server\Tests\EventListener\Response;

use Innmind\Rest\Server\EventListener\Response\DeleteListener;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DeleteListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;

    public function setUp()
    {
        $this->l = new DeleteListener;
    }

    public function testBuildResponse()
    {
        $event = new ResponseEvent(
            new Definition('foo'),
            $response = new Response,
            new Request,
            null,
            'delete'
        );

        $this->assertSame(
            null,
            $this->l->buildResponse($event)
        );
        $this->assertSame(
            204,
            $response->getStatusCode()
        );
    }

    public function testDoesntBuildResponse()
    {
        $event = new ResponseEvent(
            new Definition('foo'),
            $response = new Response,
            new Request,
            null,
            'get'
        );

        $this->l->buildResponse($event);
        $this->assertSame(
            200,
            $response->getStatusCode()
        );
    }
}
