<?php

namespace App\Controller;

use App\Repository\EmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EmailController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var EmailRepository
     */
    private EmailRepository $emailRepository;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager, EmailRepository $emailRepository)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->emailRepository = $emailRepository;
    }

    /**
     * @Route("/email", name="email_index", methods="GET")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show()
    {
        return $this->createJsonResponse(
            $this->emailRepository->findAll()
        );
    }
}
