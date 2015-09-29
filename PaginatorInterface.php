<?php

namespace Innmind\Rest\Server;

use Symfony\Component\HttpFoundation\Request;

interface PaginatorInterface
{
    /**
     * Check if the request can be paginated
     *
     * @param Request $request
     *
     * @return bool
     */
    public function canPaginate(Request $request);

    /**
     * Return the pagination offset
     *
     * @param Request $request
     *
     * @return int
     */
    public function getOffset(Request $request);

    /**
     * Return the number of resources to return
     *
     * @param Request $request
     *
     * @return int
     */
    public function getLimit(Request $request);
}
