<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\PaginatorInterface;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Neo4j;
use Innmind\Rest\Server\Event\Doctrine;
use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Routing\RouteActions;
use Innmind\Rest\Server\Routing\RouteFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class PaginationListener implements EventSubscriberInterface
{
    protected $requestStack;
    protected $urlGenerator;
    protected $routeFactory;
    protected $paginator;

    public function __construct(
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        RouteFactory $routeFactory,
        PaginatorInterface $paginator
    ) {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->routeFactory = $routeFactory;
        $this->paginator = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::NEO4J_READ_QUERY_BUILDER => 'paginateNeo4j',
            Events::DOCTRINE_READ_QUERY_BUILDER => 'paginateDoctrine',
            KernelEvents::RESPONSE => 'addPageLinks',
        ];
    }

    /**
     * Add an offset and limit to the neo4j query if info found in the request
     *
     * @param Neo4j\ReadQueryBuilderEvent $event
     *
     * @return void
     */
    public function paginateNeo4j(Neo4j\ReadQueryBuilderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$this->paginator->canPaginate($request)) {
            return;
        }

        $offset = $this->paginator->getOffset($request);
        $limit = $this->paginator->getLimit($request);

        $qb = $event->getQueryBuilder();
        $qb->toReturn('r');

        if ((int) $offset > 0) {
            $qb->skip((int) $offset);
        }

        $qb->limit((int) $limit);
        $event->replaceQueryBuilder($qb);
    }

    /**
     * Paginate a doctrine query if info found in the request
     *
     * @param Doctrine\ReadQueryBuilderEvent $event
     *
     * @return void
     */
    public function paginateDoctrine(Doctrine\ReadQueryBuilderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$this->paginator->canPaginate($request)) {
            return;
        }

        $offset = $this->paginator->getOffset($request);
        $limit = $this->paginator->getLimit($request);

        $event
            ->getQueryBuilder()
            ->setMaxResults((int) $limit);

        if ((int) $offset > 0) {
            $event
                ->getQueryBuilder()
                ->setFirstResult((int) $offset);
        }
    }

    /**
     * Add links in the response for previous or next page
     *
     * @param FilterResponseEvent $event
     *
     * @return void
     */
    public function addPageLinks(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if (
            !$request->attributes->has(RouteKeys::ACTION) ||
            $request->attributes->get(RouteKeys::ACTION) !== RouteActions::INDEX ||
            !$this->paginator->canPaginate($request)
        ) {
            return;
        }

        $offset = $this->paginator->getOffset($request);
        $limit = $this->paginator->getLimit($request);
        $response = $event->getResponse();
        $links = $response->headers->get('Link', null, false);
        $definition = $this
            ->requestStack
            ->getCurrentRequest()
            ->attributes
            ->get(RouteKeys::DEFINITION);
        $route = $this->routeFactory->makeName($definition, RouteActions::INDEX);

        if ($offset > 0) {
            $prevOffset = max(0, $offset - $limit);
            $links[] = sprintf(
                '<%s>; rel="prev"',
                $this->urlGenerator->generate(
                    $route,
                    [
                        'offset' => $prevOffset,
                        'limit' => $limit,
                    ]
                )
            );
        }

        $nextOffset = $offset + $limit;
        $links[] = sprintf(
            '<%s>; rel="next"',
            $this->urlGenerator->generate(
                $route,
                [
                    'offset' => $nextOffset,
                    'limit' => $limit,
                ]
            )
        );
        $response->headers->add(['Link' => $links]);
    }
}
