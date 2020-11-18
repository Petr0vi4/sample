<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Account;
use App\Message\CreateAccount;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateAccountHandler implements MessageHandlerInterface
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

    public function __invoke(CreateAccount $message)
    {
        $account = new Account();
        $account->setUserId($message->getUserId());
        $account->setAmount(0);
        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }
}
