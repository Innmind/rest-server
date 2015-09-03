<?php

namespace Innmind\Rest\Server\Tests\Event;

use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseEventTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildEvent()
    {
        $event = new ResponseEvent(
            $def = new Definition('foo'),
            $response = new Response,
            $request = new Request,
            'foo',
            'index'
        );
        $this->assertSame(
            $def,
            $event->getDefinition()
        );
        $this->assertSame(
            $response,
            $event->getResponse()
        );
        $this->assertSame(
            $request,
            $event->getRequest()
        );
        $this->assertSame(
            'foo',
            $event->getContent()
        );
        $this->assertSame(
            'index',
            $event->getAction()
        );
    }
}
