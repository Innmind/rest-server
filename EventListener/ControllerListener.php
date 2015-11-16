<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Routing\RouteActions;
use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Access;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Validator;
use Innmind\Rest\Server\Request\Parser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ControllerListener implements EventSubscriberInterface
{
    protected $validator;
    protected $requestParser;

    public function __construct(
        Validator $validator,
        Parser $parser
    ) {
        $this->validator = $validator;
        $this->requestParser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'decodeRequest',
            KernelEvents::VIEW => [['validateControllerResult', 20]],
        ];
    }

    /**
     * Decode the content from the request (if necessary) and validate it
     *
     * @param FilterControllerEvent $event
     *
     * @return void
     */
    public function decodeRequest(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return;
        }

        $action = $request->attributes->get(RouteKeys::ACTION);

        switch ($action) {
            case RouteActions::INDEX:
            case RouteActions::GET:
            case RouteActions::DELETE:
            case RouteActions::OPTIONS:
                $request->attributes->set(
                    'definition',
                    $request->attributes->get(RouteKeys::DEFINITION)
                );
                break;
            case RouteActions::CREATE:
                $data = $this->requestParser->getData(
                    $request,
                    $request->attributes->get(RouteKeys::DEFINITION)
                );
                $this->validate($data, Access::CREATE);
                $request->attributes->set('resources', $data);
                break;
            case RouteActions::UPDATE:
                $resource = $this->requestParser->getData(
                    $request,
                    $request->attributes->get(RouteKeys::DEFINITION)
                );

                if ($resource instanceof Collection) {
                    throw new PayloadException(
                        'You can only update one resource at a time'
                    );
                }

                $this->validate($resource, Access::UPDATE);
                $request->attributes->set('resource', $resource);
                break;
        }
    }

    /**
     * Validate the content returned by the controller and build the response
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @return void
     */
    public function validateControllerResult(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return;
        }

        $action = $request->attributes->get(RouteKeys::ACTION);
        $toVerify = [
            RouteActions::INDEX,
            RouteActions::GET,
            RouteActions::CREATE,
            RouteActions::UPDATE,
        ];

        if (in_array($action, $toVerify, true)) {
            $this->validate($event->getControllerResult(), Access::READ);
        }
    }

    /**
     * Validate the given data for the given access
     *
     * @param Innmind\Rest\Resource|Collection $data
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
            throw ValidationException::build($access, $violations);
        }
    }
}