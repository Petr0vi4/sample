<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EnvelopeHandler implements MessageHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Envelope $message)
    {
        $this->messageBus->dispatch($message->getPayload());
    }
}
