<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Session;
use App\Dto\User;
use Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class SessionRepository
{
    /**
     * @var Client
     */
    private Client $redisClient;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(Client $redisClient, SerializerInterface $serializer)
    {
        $this->redisClient = $redisClient;
        $this->serializer = $serializer;
    }

    public function create(User $user): Session
    {
        $session = new Session();
        $session->setId((string) Uuid::v4());
        $session->setUser($user);

        $this->save($session);

        return $session;
    }

    public function find(string $id): ?Session
    {
        $data = $this->redisClient->get($id);
        if (empty($data)) {
            return null;
        }

        /** @var Session $session */
        $session = $this->serializer->deserialize($data, Session::class, 'json');

        return $session;
    }

    public function delete(string $id): void
    {
        $this->redisClient->del($id);
    }

    private function save(Session $session): void
    {
        $data = $this->serializer->serialize($session, 'json');
        $this->redisClient->set($session->getId(), $data);
    }
}
