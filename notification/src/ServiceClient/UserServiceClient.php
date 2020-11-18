<?php

declare(strict_types=1);

namespace App\ServiceClient;

use App\Dto\User;
use GuzzleHttp\Client;
use Symfony\Component\Serializer\SerializerInterface;

class UserServiceClient
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer, string $baseUri)
    {
        $this->client = new Client(['base_uri' => $baseUri]);
        $this->serializer = $serializer;
    }

    public function getById(int $userId): ?User
    {
        $response = $this->client->get("/user/$userId");

        /** @var User $user */
        $user = $this->serializer->deserialize((string) $response->getBody(), User::class, 'json');

        return $user;
    }
}
