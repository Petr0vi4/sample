<?php

namespace App\Controller;

use App\Controller\InputValue\LoginInputValue;
use App\Controller\InputValue\RegisterInputValue;
use App\Repository\SessionRepository;
use App\ServiceClient\AppServiceClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

class UserController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * @var SessionRepository
     */
    private SessionRepository $sessionRepository;

    /**
     * @var AppServiceClient
     */
    private AppServiceClient $appServiceClient;

    public function __construct(
        SerializerInterface $serializer,
        SessionRepository $sessionRepository,
        AppServiceClient $appServiceClient
    ) {
        $this->serializer = $serializer;
        $this->sessionRepository = $sessionRepository;
        $this->appServiceClient = $appServiceClient;
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
        if (null === $sessionId || null === $session = $this->sessionRepository->find($sessionId)) {
            return $this->createNotAuthorizedResponse();
        }

        $response = $this->createJsonResponse(['status' => 'ok']);
        $response->headers->set('X-UserId', $session->getUser()->getId());
        $response->headers->set('X-User', $session->getUser()->getUsername());
        $response->headers->set('X-First-Name', $session->getUser()->getFirstName());
        $response->headers->set('X-Last-Name', $session->getUser()->getLastName());
        $response->headers->set('X-Email', $session->getUser()->getEmail());

        return $response;
    }

    /**
     * @Route("/register", name="user_register", methods="POST")
     *
     * @param RegisterInputValue $value
     *
     * @return JsonResponse
     */
    public function register(RegisterInputValue $value)
    {
        $user = $this->appServiceClient->register(
            $value->getUsername(),
            $value->getPassword(),
            $value->getFirstName(),
            $value->getLastName(),
            $value->getEmail()
        );

        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/login", name="user_login", methods="POST")
     *
     * @param LoginInputValue $value
     *
     * @return Response
     */
    public function login(LoginInputValue $value)
    {
        try {
            $user = $this->appServiceClient->login($value->getUsername(), $value->getPassword());
        } catch (Throwable $e) {
            return $this->createNotAuthorizedResponse();
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
