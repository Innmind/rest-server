<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\SpecificationBuilder;

use Innmind\Rest\Server\{
    Exception\NoFilterFoundException,
    Definition\HttpResource
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Specification\SpecificationInterface;

interface BuilderInterface
{
    /**
     * Transform request filters into a specification
     *
     * @param ServerRequest $request
     * @param HttpResource $definition
     *
     * @throws NoFilterFoundException
     * @throws FilterNotApplicableException
     *
     * @return SpecificationInterface
     */
    public function buildFrom(
        ServerRequest $request,
        HttpResource $definition
    ): SpecificationInterface;
}
