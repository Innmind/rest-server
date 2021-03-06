<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\RequestDecoder;

use Innmind\Rest\Server\Serializer\{
    RequestDecoder\Form,
    RequestDecoder,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Form as HttpForm,
    Message\Form\Parameter,
};
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(RequestDecoder::class, new Form);
    }

    public function testInvokation()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->once())
            ->method('form')
            ->willReturn(HttpForm::of(
                new Parameter('foo', '42'),
                new Parameter(
                    'bar',
                    ['baz' => '24'],
                )
            ));
        $data = (new Form)($request);

        $this->assertSame(['foo' => '42', 'bar' => ['baz' => '24']], $data);
    }
}
