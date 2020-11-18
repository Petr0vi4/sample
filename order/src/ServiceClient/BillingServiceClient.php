<?php

declare(strict_types=1);

namespace App\ServiceClient;

use App\Dto\Account;
use App\ServiceClient\BillingServiceClient\NotEnoughAmountException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BillingServiceClient
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

    /**
     * @param $uri
     * @param $options
     *
     * @return ResponseInterface
     * @throws NotEnoughAmountException
     */
    private function post($uri, $options): ResponseInterface
    {
        try {
            return $this->client->post($uri, $options);
        } catch (ClientException $e) {
            $error = $e->hasResponse() ? json_decode((string) $e->getResponse()->getBody(), true) : null;
            if (empty($error['code'])) {
                throw $e;
            } else {
                switch ($error['code']) {
                    case 1000:
                        throw new NotEnoughAmountException($error['error'] ?? '');
                    default:
                        throw $e;
                }
            }
        }
    }

    /**
     * @param int $userId
     * @param float $amount
     *
     * @return Account
     * @throws NotEnoughAmountException
     */
    public function withdraw(int $userId, float $amount): Account
    {
        $response = $this->post(
            "/account/$userId/withdraw",
            ['body' => json_encode(compact('amount'))]
        );

        /** @var Account $account */
        $account = $this->serializer->deserialize((string) $response->getBody(), Account::class, 'json');

        return $account;
    }
}
