<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Email;
use App\Message\NotifyAboutOrderCreated;
use App\ServiceClient\OrderServiceClient;
use App\ServiceClient\UserServiceClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotifyAboutOrderCreatedHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var OrderServiceClient
     */
    private OrderServiceClient $orderServiceClient;

    /**
     * @var UserServiceClient
     */
    private UserServiceClient $userServiceClient;

    public function __construct(EntityManagerInterface $entityManager, OrderServiceClient $orderServiceClient, UserServiceClient $userServiceClient)
    {
        $this->entityManager = $entityManager;
        $this->orderServiceClient = $orderServiceClient;
        $this->userServiceClient = $userServiceClient;
    }

    public function __invoke(NotifyAboutOrderCreated $message)
    {
        $order = $this->orderServiceClient->getById($message->getOrderId());
        $user = $this->userServiceClient->getById($order->getUserId());

        $email = new Email();
        $email->setFromAddress('notification-service@mail.ru');
        $email->setToAddress($user->getEmail());
        if ($order->getStatus() === 'paid') {
            $body = sprintf('Order №%s is paid', (string) $order->getId());
        } elseif ($order->getStatus() === 'error') {
            $body = sprintf('Order №%s has error', (string) $order->getId());
        } else {
            $body = sprintf('Order №%s is created', (string) $order->getId());
        }
        $email->setMessageBody($body);
        $email->setSent(false);
        $this->entityManager->persist($email);
        $this->entityManager->flush();
    }
}
