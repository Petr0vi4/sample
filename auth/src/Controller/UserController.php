<?php

namespace App\Controller;

use App\Controller\InputValue\CreateUserInputValue;
use App\Controller\InputValue\UpdateUserInputValue;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/user", name="user_create", methods="POST")
     *
     * @param CreateUserInputValue $value
     *
     * @return JsonResponse
     */
    public function create(CreateUserInputValue $value)
    {
        $user = new User();
        $user
            ->setUsername($value->getUsername())
            ->setFirstName($value->getFirstName())
            ->setLastName($value->getLastName())
            ->setEmail($value->getEmail())
            ->setPhone($value->getPhone());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->createJsonResponse($user, Response::HTTP_CREATED);
    }

    /**
     * @Route("/user/{id}", name="user_show", methods="GET", requirements={"id"="\d+"})
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function show(User $user)
    {
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/user/{id}", name="user_update", methods="PUT", requirements={"id"="\d+"})
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @param User $user
     * @param UpdateUserInputValue $value
     *
     * @return JsonResponse
     */
    public function update(User $user, UpdateUserInputValue $value)
    {
        $user
            ->setUsername($value->getUsername())
            ->setFirstName($value->getFirstName())
            ->setLastName($value->getLastName())
            ->setEmail($value->getEmail())
            ->setPhone($value->getPhone());
        $this->entityManager->flush();

        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/user/{id}", name="user_delete", methods="DELETE", requirements={"id"="\d+"})
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @param User $user
     *
     * @return Response
     */
    public function delete(User $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
