<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\RequestEvent;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Event\Neo4j;
use Innmind\Rest\Server\Event\Doctrine;
use Innmind\Rest\Server\RouteLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginationListener implements EventSubscriberInterface
{
    protected $request;
    protected $definition;
    protected $urlGenerator;
    protected $routeLoader;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RouteLoader $routeLoader
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routeLoader = $routeLoader;
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
            Events::REQUEST => 'keepRequest',
        ];
    }

    /**
     * Keep the request being handled
     *
     * @param RequestEvent $event
     *
     * @return void
     */
    public function keepRequest(RequestEvent $event)
    {
        $this->request = $event->getRequest();
        $this->definition = $event->getDefinition();
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
        if (!$this->canPaginate()) {
            return;
        }

        list($offset, $limit) = $this->getPaginationBounds();

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
        if (!$this->canPaginate()) {
            return;
        }

        list($offset, $limit) = $this->getPaginationBounds();

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
        if ($this->request !== $event->getRequest()) {
            return;
        }

        if ($event->getAction() !== 'index') {
            return;
        }

        if (!$this->canPaginate()) {
            return;
        }

        list($offset, $limit) = $this->getPaginationBounds();
        $response = $event->getResponse();
        $links = $response->headers->get('Link', null, false);
        $route = $this->routeLoader->getRoute(
            $this->definition,
            'index'
        );

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

    /**
     * Check if request has pagination info
     *
     * @return bool
     */
    protected function canPaginate()
    {
        if (!$this->request) {
            return false;
        }

        if (!$this->request->query->has('limit')) {
            if ($this->definition->hasOption('paginate')) {
                return true;
            }

            return false;
        }

        $offset = $this->request->query->get('offset', 0);
        $limit = $this->request->query->get('limit');

        if (!is_numeric($offset) || !is_numeric($limit)) {
            return false;
        }

        return true;
    }

    /**
     * Return the offset and limit for pagination
     *
     * @return array
     */
    protected function getPaginationBounds()
    {
        $offset = (int) $this->request->query->get('offset', 0);
        $limit = $this->request->query->has('limit') ?
            (int) $this->request->query->get('limit') :
            (int) $this->definition->getOption('paginate');

        return [$offset, $limit];
    }
}
