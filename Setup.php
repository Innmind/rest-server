<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Routing\RouteCollection;
use Innmind\Rest\Server\Request\Handler as RequestHandler;
use Innmind\Rest\Server\Request\Parser as RequestParser;
use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\EventListener\Response as ResponseListener;
use Innmind\Rest\Server\EventListener\StorageCreateListener;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Serializer;
use Negotiation\Negotiator;

class Setup
{
    protected $dispatcher;
    protected $registry;
    protected $storages;
    protected $entityBuilder;
    protected $resourceBuilder;
    protected $routes;
    protected $routeLoader;
    protected $requestHandler;
    protected $requestParser;
    protected $validator;
    protected $serializer;
    protected $responseSubscribersLoaded = false;

    /**
     * @param string $config
     * @param string $prefix
     * @param array $storages
     * @param Serializer $serializer
     * @param array $compilerPasses
     * @param EventDispatcherInterface|null $dispatcher
     * @param ValidatorInterface|null $validator
     * @param PropertyAccessorInterface|null $accessor
     */
    public function __construct(
        $config,
        array $storages,
        Serializer $serializer,
        $prefix = null,
        array $compilerPasses = [],
        EventDispatcherInterface $dispatcher = null,
        ValidatorInterface $validator = null,
        PropertyAccessorInterface $accessor = null
    ) {
        if ($dispatcher === null) {
            $dispatcher = new EventDispatcher;
        }

        if ($validator === null) {
            $validator = Validation::createValidator();
        }

        if ($accessor === null) {
            $accessor = PropertyAccess::createPropertyAccessor();
        }

        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
        $this
            ->buildStorages($storages)
            ->buildRegistry($config, $compilerPasses)
            ->buildEntityBuilder($accessor, $dispatcher)
            ->buildResourceBuilder($accessor, $dispatcher)
            ->buildRouteLoader($dispatcher, $prefix)
            ->buildValidator($validator)
            ->buildRequestParser()
            ->buildRequestHandler();

        $this->dispatcher->addSubscriber(new StorageCreateListener($accessor));
    }

    /**
     * Add a supported format
     *
     * @param string $name
     * @param string $mediaType
     * @param int $priority
     *
     * @return Setup self
     */
    public function addFormat($name, $mediaType, $priority)
    {
        $this->formats->add($name, $mediaType, $priority);

        return $this;
    }

    /**
     * Handle a request
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handleRequest(Request $request)
    {
        $this->handleRouting($request);
        $this->verifyRequest($request);

        $request->attributes->set(
            '_requested_format',
            $this->requestParser->getRequestedFormat($request)
        );
        $definition = $request->attributes->get(RouteCollection::RESOURCE_KEY);
        $action = $request->attributes->get(RouteCollection::ACTION_KEY);

        switch ($action) {
            case 'index':
                $content = $this->handleIndexAction($definition);
                break;
            case 'get':
                $content = $this->handleGetAction($request, $definition);
                break;
            case 'create':
                $content = $this->handleCreateAction($request, $definition);
                break;
            case 'update':
                $content = $this->handleUpdateAction($request, $definition);
                break;
            case 'delete':
                $this->requestHandler->deleteAction(
                    $definition,
                    $request->attributes->get('id')
                );
                $content = null;
                break;
            case 'options':
                $content = $this->requestHandler->optionsAction($definition);
                break;
        }

        $response = new Response;

        $this->dispatcher->dispatch(
            Events::RESPONSE,
            new ResponseEvent(
                $definition,
                $response,
                $request,
                $content,
                $action
            )
        );

        return $response;
    }

    /**
     * Attach response subscribers to the dispatcher
     *
     * @param UrlGenerator $urlGenrator
     *
     * @return void
     */
    protected function attachSubscribers(UrlGenerator $urlGenerator)
    {
        if ($this->responseSubscribersLoaded === true) {
            return;
        }

        $this->dispatcher->addSubscriber(new ResponseListener\OptionsListener(
            $urlGenerator,
            $this->routeLoader
        ));
        $this->dispatcher->addSubscriber(new ResponseListener\CollectionListener(
            $urlGenerator,
            $this->routeLoader
        ));
        $this->dispatcher->addSubscriber(new ResponseListener\CreateListener(
            $urlGenerator,
            $this->routeLoader
        ));
        $this->dispatcher->addSubscriber(new ResponseListener\DeleteListener);
        $this->dispatcher->addSubscriber(new ResponseListener\ResourceListener(
            $urlGenerator,
            $this->routeLoader,
            $this->serializer
        ));

        $this->responseSubscribersLoaded = true;
    }

    /**
     * Build the storages holder
     *
     * @param array $storages
     *
     * @return Setup self
     */
    protected function buildStorages(array $storages)
    {
        $this->storages = new Storages;

        foreach ($storages as $name => $storage) {
            $this->storages->add($name, $storage);
        }

        return $this;
    }

    /**
     * Build the resource definition registry
     *
     * @param string $config Path to the config file
     * @param array $compilerPasses
     *
     * @return Setup self
     */
    protected function buildRegistry($config, array $compilerPasses)
    {
        $this->registry = new Registry;
        $this->registry->load(Yaml::parse(file_get_contents((string) $config)));

        $compiler = new DefinitionCompiler;
        $compilerPasses = array_merge(
            [
                new CompilerPass\AccessPass,
                new CompilerPass\ArrayTypePass,
                new CompilerPass\SubResourcePass,
                new CompilerPass\StorageValidationPass($this->storages),
            ],
            $compilerPasses
        );

        foreach ($compilerPasses as $pass) {
            $compiler->addCompilerPass($pass);
        }

        $compiler->process($this->registry);

        return $this;
    }

    /**
     * Instanciate an entity builder
     *
     * @param PropertyAccessorInterface $accessor
     * @param EventDispatcherInterface $dispatcher
     *
     * @return Setup self
     */
    protected function buildEntityBuilder(
        PropertyAccessorInterface $accessor,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityBuilder = new EntityBuilder($accessor, $dispatcher);

        return $this;
    }

    /**
     * Instanciate a resource builder
     *
     * @param PropertyAccessorInterface $accessor
     * @param EventDispatcherInterface $dispatcher
     *
     * @return Setup self
     */
    protected function buildResourceBuilder(
        PropertyAccessorInterface $accessor,
        EventDispatcherInterface $dispatcher
    ) {
        $this->resourceBuilder = new ResourceBuilder($accessor, $dispatcher);

        return $this;
    }

    /**
     * Build the resource loader
     *
     * @param EventDispatcherInterface $dispatcher
     * @param string $prefix Uri prefix
     *
     * @return Setup self
     */
    protected function buildRouteLoader(
        EventDispatcherInterface $dispatcher,
        $prefix
    ) {
        $this->routeLoader = new RouteLoader($dispatcher, $this->registry, $prefix);
        $this->routes = $this->routeLoader->load('.');

        return $this;
    }

    /**
     * Build the resource validator
     *
     * @param ValidatorInterface $validator
     *
     * @return Setup self
     */
    protected function buildValidator(ValidatorInterface $validator)
    {
        $this->validator = new Validator($validator);

        return $this;
    }

    /**
     * Build the request paser
     *
     * @return Setup self
     */
    protected function buildRequestParser()
    {
        $this->formats = new Formats;
        $this->requestParser = new RequestParser(
            $this->serializer,
            $this->formats,
            new Negotiator
        );

        return $this;
    }

    /**
     * Build the request handler
     *
     * @return Setup self
     */
    protected function buildRequestHandler()
    {
        $this->requestHandler = new RequestHandler(
            $this->storages,
            $this->resourceBuilder
        );

        return $this;
    }

    /**
     * Handle the route matching
     *
     * @param Request $request
     *
     * @return void
     */
    protected function handleRouting(Request $request)
    {
        $context = new RequestContext;
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routes, $context);
        $urlGenerator = new UrlGenerator($this->routes, $context);
        $this->attachSubscribers($urlGenerator);

        $parameters = $matcher->matchRequest($request);

        foreach ($parameters as $key => $value) {
            $request->attributes->set($key, $value);
        }
    }

    /**
     * Verify the content type or the accept type can be handled
     *
     * @param Request $request
     *
     * @throws UnsupportedMediaTypeHttpException If the content type can't be decoded
     * @throws NotAcceptableHttpException If we can't serve content with the wishd format
     *
     * @return void
     */
    protected function verifyRequest(Request $request)
    {
        $action = $request->attributes->get(RouteCollection::ACTION_KEY);

        if (
            in_array($action, ['create', 'update']) &&
            !$this->requestParser->isContentTypeAcceptable($request)
        ) {
            throw new UnsupportedMediaTypeHttpException;
        }

        if (!$this->requestParser->isRequestedTypeAcceptable($request)) {
            throw new NotAcceptableHttpException;
        }
    }

    /**
     * Handle the index action
     *
     * @param Definition $definition
     *
     * @return Collection
     */
    protected function handleIndexAction(Definition $definition)
    {
        $content = $this->requestHandler->indexAction($definition);
        $this->validate($content, Access::READ);

        return $content;
    }

    /**
     * Handle the GET action
     *
     * @param Request $request
     * @param Definition $definition
     *
     * @return Resource
     */
    protected function handleGetAction(Request $request, Definition $definition)
    {
        $content = $this->requestHandler->getAction(
            $definition,
            $request->attributes->get('id')
        );
        $this->validate($content, Access::READ);

        return $content;
    }

    /**
     * Handle the creation of a resource or collection of ones
     *
     * @param Request $request
     * @param Definition $definition
     *
     * @return Resource|Collection
     */
    protected function handleCreateAction(
        Request $request,
        Definition $definition
    ) {
        $data = $this->requestParser->getData($request, $definition);
        $this->validate($data, Access::CREATE);

        $resources = new Collection;

        if ($data instanceof Collection) {
            $resources = $data;
        } else {
            $resources[] = $data;
        }

        foreach ($resources as $resource) {
            $this->requestHandler->createAction($resource);
        }

        return $data;
    }

    /**
     * Handle the update of a resource
     *
     * @param Request $request
     * @param Definition $definition
     *
     * @throws PayloadException If attempt to update multiple resources
     *
     * @return Resource
     */
    protected function handleUpdateAction(
        Request $request,
        Definition $definition
    ) {
        $resource = $this->requestParser->getData($request, $definition);

        if ($resource instanceof Collection) {
            throw new PayloadException(
                'You can only update one resource at a time'
            );
        }

        $this->validate($resource, Access::UPDATE);
        $this->requestHandler->updateAction(
            $resource,
            $request->attributes->get('id')
        );

        return $resource;
    }

    /**
     * Validate the given content for the wished access
     *
     * @param Resource|Collection $data
     * @param string $access
     *
     * @throws ValidationException
     *
     * @return void
     */
    protected function validate($data, $access)
    {
        $violations = $this->validator->validate($data, $access);

        if ($violations->count() > 0) {
            throw ValidationException::build(Access::READ, $violations);
        }
    }
}
