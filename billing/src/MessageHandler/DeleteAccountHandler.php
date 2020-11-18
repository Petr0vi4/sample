<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\DeleteAccount;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteAccountHandler implements MessageHandlerInterface
{
    /**
     * @var AccountRepository
     */
    private AccountRepository $accountRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(AccountRepository $accountRepository, EntityManagerInterface $entityManager)
    {
        $this->accountRepository = $accountRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteAccount $message)
    {
        $account = $this->accountRepository->findOneBy(['userId' => $message->getUserId()]);
        if (null !== $account) {
            $this->entityManager->remove($account);
            $this->entityManager->flush();
        }
    }
}
