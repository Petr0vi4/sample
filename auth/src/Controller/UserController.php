<?php

namespace App\Controller;

use App\Controller\InputValue\LoginInputValue;
use App\Controller\InputValue\RegisterInputValue;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
    private $userRepository;

    /**
     * @var SessionRepository
     */
    private SessionRepository $sessionRepository;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SessionRepository $sessionRepository
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @Route("/auth", name="user_auth")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function auth(Request $request)
    {
        $sessionId = $request->cookies->get('session_id');
        if (null === $sessionId) {
            return $this->createNotAuthorizedResponse();
        }
        $session = $this->sessionRepository->find($sessionId);
        if (null === $session) {
            return $this->createNotAuthorizedResponse();
        }

        $response = $this->createJsonResponse(['status' => 'ok']);
        $response->headers->set('X-UserId', $session->getUserId());
        $response->headers->set('X-User', $session->getUsername());
        $response->headers->set('X-First-Name', $session->getFirstName());
        $response->headers->set('X-Last-Name', $session->getLastName());
        $response->headers->set('X-Email', $session->getEmail());

        return $response;
    }

    /**
     * @Route("/register", name="user_register", methods="POST")
     *
     * @param RegisterInputValue $value
     *
     * @return Response
     */
    public function register(RegisterInputValue $value)
    {
        $user = $this->userRepository->findOneBy(['username' => $value->getUsername()]);
        if (null !== $user) {
            return $this->createNotAuthorizedResponse();
        }
        $user = new User();
        $user->setUsername($value->getUsername());
        $user->setPassword($value->getPassword());
        $user->setFirstName($value->getFirstName());
        $user->setLastName($value->getLastName());
        $user->setEmail($value->getEmail());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->createJsonResponse(['id' => $user->getId()], Response::HTTP_CREATED);
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
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        $session = $this->sessionRepository->create($user);

        $response = $this->createJsonResponse(['status' => 'ok']);
        $response->headers->setCookie(new Cookie('session_id', $session->getId()));

        return $response;
    }

    /**
     * @Route("/logout", name="user_logout")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $sessionId = $request->cookies->get('session_id');
        if (null !== $sessionId) {
            $this->sessionRepository->delete($sessionId);
        }

        $response = $this->createJsonResponse(['status' => 'ok']);
        $response->headers->clearCookie('session_id');

        return $response;
    }

    private function createNotAuthorizedResponse(): Response
    {
        return new Response(null, Response::HTTP_UNAUTHORIZED, [
            'WWW-Authenticate' => 'Basic realm="Sample Realm"'
        ]);
    }
}
