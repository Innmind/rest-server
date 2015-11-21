<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Routing\RouteKeys;
use Symfony\Component\HttpFoundation\Request;

class Paginator implements PaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function canPaginate(Request $request)
    {
        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return false;
        }

        $definition = $request->attributes->get(RouteKeys::DEFINITION);

        if (!$request->query->has('limit')) {
            if ($definition->hasOption('paginate')) {
                return true;
            }

            return false;
        }

        $offset = $request->query->get('offset', 0);
        $limit = $request->query->get('limit');

        if (!is_numeric($offset) || !is_numeric($limit)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset(Request $request)
    {
        return (int) $request->query->get('offset', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit(Request $request)
    {
        $definition = $request->attributes->get(RouteKeys::DEFINITION);

        return $request->query->has('limit') ?
            (int) $request->query->get('limit') :
            (int) $definition->getOption('paginate');
    }
}
