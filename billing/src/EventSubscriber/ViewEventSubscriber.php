<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class ViewEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'onKernelView',
        ];
    }

    public function onKernelView(ViewEvent $event)
    {
        $data = $this->serializer->serialize($event->getControllerResult(), 'json');

        $event->setResponse(
            new JsonResponse(
                $data,
                200,
                [
                    'Content-Type'   => 'application/json',
                    'Content-Length' => strlen($data)
                ],
                true
            )
        );
    }
}
