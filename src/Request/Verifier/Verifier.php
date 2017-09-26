<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\Message\ServerRequest;

interface Verifier
{
    /**
     * A verifier must throw an exception if the request is not coherent with
     * the action it is asked to do or the server can't answer to it
     *
     * @return void
     */
    public function verify(
        ServerRequest $request,
        HttpResource $definition
    );
}
