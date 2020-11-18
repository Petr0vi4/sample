<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class HealthCheckController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/health", name="health_check", methods="GET")
     */
    public function index()
    {
        return $this->createJsonResponse([
            'status' => 'OK',
        ]);
    }
}
