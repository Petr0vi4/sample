<?php

declare(strict_types=1);

namespace App\ServiceClient;

use App\Dto\User;
use GuzzleHttp\Client;
use Symfony\Component\Serializer\SerializerInterface;

class AppServiceClient
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

    public function register(string $username, string $password, string $firstName, string $lastName, string $email): User
    {
        $response = $this->client->post(
            '/user',
            ['body' => json_encode(compact('username', 'password', 'firstName', 'lastName', 'email'))]
        );

        /** @var User $user */
        $user = $this->serializer->deserialize((string) $response->getBody(), User::class, 'json');

        return $user;
    }

    public function login(string $username, string $password): ?User
    {
        $response = $this->client->post('/login', ['body' => json_encode(compact('username', 'password'))]);

        /** @var User $user */
        $user = $this->serializer->deserialize((string) $response->getBody(), User::class, 'json');

        return $user;
    }
}
