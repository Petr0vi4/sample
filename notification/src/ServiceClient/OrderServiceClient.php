<?php

declare(strict_types=1);

namespace App\ServiceClient;

use App\Dto\Order;
use GuzzleHttp\Client;
use Symfony\Component\Serializer\SerializerInterface;

class OrderServiceClient
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

    public function getById(int $orderId): ?Order
    {
        $response = $this->client->get("/order/$orderId");

        /** @var Order $order */
        $order = $this->serializer->deserialize((string) $response->getBody(), Order::class, 'json');

        return $order;
    }
}
