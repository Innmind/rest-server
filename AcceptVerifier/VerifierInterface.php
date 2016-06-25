<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\AcceptVerifier;

use Innmind\Http\Message\ServerRequestInterface;

interface VerifierInterface
{
    /**
     * @throws NotAcceptableException
     *
     * @return void
     */
    public function verify(ServerRequestInterface $request);
}
