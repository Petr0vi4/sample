<?php

namespace App\Controller;

use App\Controller\InputValue\CreateUserInputValue;
use App\Controller\InputValue\LoginInputValue;
use App\Controller\InputValue\UpdateUserInputValue;
use App\Entity\User;
use App\Message\CreateAccount;
use App\Message\DeleteAccount;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $messageBus;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager, UserRepository $userRepository, MessageBusInterface $messageBus)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->messageBus = $messageBus;
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
        $existingUser = $this->userRepository->findOneBy(['username' => $value->getUsername()]);
        if (null !== $existingUser) {
            throw new BadRequestHttpException('User already exists');
        }

        $user = new User();
        $user->setUsername($value->getUsername());
        $user->setPassword($value->getPassword());
        $user->setFirstName($value->getFirstName());
        $user->setLastName($value->getLastName());
        $user->setEmail($value->getEmail());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new CreateAccount($user->getId()));

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
        $user->setUsername($value->getUsername());
        $user->setPassword($value->getPassword());
        $user->setFirstName($value->getFirstName());
        $user->setLastName($value->getLastName());
        $user->setEmail($value->getEmail());
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
        $userId = $user->getId();
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new DeleteAccount($userId));

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/login", name="user_login", methods="POST")
     *
     * @param LoginInputValue $value
     *
     * @return JsonResponse
     */
    public function login(LoginInputValue $value)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['username' => $value->getUsername()]);
        if (null === $user || $user->getPassword() !== $value->getPassword()) {
            throw new BadRequestHttpException('Invalid login or password');
        }

        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/me", name="user_me", methods={"GET","PUT"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function me(Request $request)
    {
        $userId = $request->headers->get('X-UserId');
        if (null === $userId) {
            return $this->createJsonResponse('Not authenticated');
        }

        if ($request->isMethod('GET')) {
            return $this->forward('App\Controller\UserController::show', [
                'id'  => $userId,
            ]);
        }

        return $this->forward('App\Controller\UserController::update', [
            'id'  => $userId,
        ]);
    }
}
