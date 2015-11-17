<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;
use Innmind\Rest\Server\Access;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'buildHttpException',
        ];
    }

    /**
     * Transform an application exception into an
     * http exception so symfony can transform it
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    public function buildHttpException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        switch (true) {
            case $exception instanceof PayloadException:
                $exception = new BadRequestHttpException(null, $exception);
                break;
            case $exception instanceof ValidationException:
                if ($exception->getAccess() === Access::READ) {
                    /*
                    @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#cite_note-89
                    It means we want to return a resource that doesn't follow its description
                     */
                    $exception = new HttpException(
                        520,
                        'Resource doesn\'t match its definition',
                        $exception
                    );
                } else {
                    $exception = new BadRequestHttpException(null, $exception);
                }
                break;
            case $exception instanceof ResourceNotFoundException:
                $exception = new NotFoundHttpException(null, $exception);
                break;
            case $exception instanceof TooManyResourcesFoundException:
                $exception = new ConflictHttpException(null, $exception);
                break;
        }

        $event->setException($exception);
    }
}
