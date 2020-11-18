<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $data = json_encode(['error' => $exception->getMessage()]);
        $headers = [];
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        } elseif ($exception instanceof BadRequestException) {
            $statusCode = 400;
        } else {
            $statusCode = 500;
        }

        $event->setResponse(
            new JsonResponse(
                $data,
                $statusCode,
                array_merge(
                    [
                        'Content-Type'   => 'application/json',
                        'Content-Length' => strlen($data),
                    ],
                    $headers
                ),
                true
            )
        );
    }
}
