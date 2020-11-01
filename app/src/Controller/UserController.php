<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    use JsonResponseTrait;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/user/me", name="user_me")
     *
     * @param Request $request
     */
    public function me(Request $request)
    {
        $userId = $request->headers->get('X-UserId');
        if (null === $userId) {
            $data = 'Not authenticated';
        } else {
            $data = [
                'id' => $request->headers->get('X-UserId'),
                'login' => $request->headers->get('X-User'),
                'first_name' => $request->headers->get('X-First-Name'),
                'last_name' => $request->headers->get('X-Last-Name'),
                'email' => $request->headers->get('X-Email'),
            ];
        }

        return $this->createJsonResponse($data);
    }
}
