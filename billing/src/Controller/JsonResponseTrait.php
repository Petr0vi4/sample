<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

trait JsonResponseTrait
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    private function createJsonResponse($data, $status = Response::HTTP_OK): JsonResponse
    {
        $data = $this->serializer->serialize($data, 'json');

        return new JsonResponse(
            $data,
            $status,
            [
                'Content-Type'   => 'application/json',
                'Content-Length' => strlen($data)
            ],
            true
        );
    }
}