<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Session;
use App\Entity\User;
use Predis\Client;

class SessionRepository
{
    /**
     * @var Client
     */
    private Client $redisClient;

    public function __construct(Client $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    public function create(User $user): Session
    {
        $session = new Session();
        $session->setUserId($user->getId());
        $session->setUsername($user->getUsername());
        $session->setFirstName($user->getFirstName());
        $session->setLastName($user->getLastName());
        $session->setEmail($user->getEmail());

        $this->save($session);

        return $session;
    }

    public function find(string $id): ?Session
    {
        $data = json_decode($this->redisClient->get($id) ?? '', true);
        if (empty($data['userId'])) {
            return null;
        }

        $session = new Session($id);
        $session->setUserId($data['userId']);
        $session->setUsername($data['username'] ?? '');
        $session->setFirstName($data['firstName'] ?? '');
        $session->setLastName($data['lastName'] ?? '');
        $session->setEmail($data['email'] ?? '');

        return $session;
    }

    public function delete(string $id): void
    {
        $this->redisClient->del($id);
    }

    private function save(Session $session): void
    {
        $this->redisClient->set($session->getId(), json_encode([
            'userId' => $session->getUserId(),
            'username' => $session->getUsername(),
            'firstName' => $session->getFirstName(),
            'lastName' => $session->getLastName(),
            'email' => $session->getEmail(),
        ]));
    }
}
