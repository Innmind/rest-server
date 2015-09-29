<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\PaginatorInterface;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Neo4j;
use Innmind\Rest\Server\Event\Doctrine;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Routing\RouteCollection;
use Innmind\Rest\Server\Routing\RouteFinder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginationListener implements EventSubscriberInterface
{
    protected $requestStack;
    protected $urlGenerator;
    protected $routeFinder;
    protected $paginator;

    public function __construct(
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        RouteFinder $routeFinder,
        PaginatorInterface $paginator
    ) {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->routeFinder = $routeFinder;
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
            Events::RESPONSE => 'addPageLinks',
        ];
    }

    /**
     * Add an offset and limit to the neo4j query if info found in the request
     *
     * @param ReadQueryBuilderEvent $event
     *
     * @return void
     */
    public function paginateNeo4j(Neo4j\ReadQueryBuilderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$this->paginator->canPaginate($request)) {
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
     * @param ReadQueryBuilderEvent $event
     *
     * @return void
     */
    public function paginateDoctrine(Doctrine\ReadQueryBuilderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$this->paginator->canPaginate($request)) {
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
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function addPageLinks(ResponseEvent $event)
    {
        if ($event->getAction() !== 'index') {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$this->paginator->canPaginate($request)) {
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
            ->get(RouteCollection::RESOURCE_KEY);
        $route = $this->routeFinder->find($definition, 'index');

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

        $collection = $event->getContent();

        if ($collection->count() < $limit) {
            return;
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
