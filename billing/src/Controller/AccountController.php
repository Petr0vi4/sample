<?php

namespace App\Controller;

use App\Controller\InputValue\AccountWithdrawInputValue;
use App\Controller\InputValue\CreateAccountInputValue;
use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AccountController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var AccountRepository
     */
    private AccountRepository $accountRepository;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager, AccountRepository $accountRepository)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @Route("/account", name="account_create", methods="POST")
     *
     * @param CreateAccountInputValue $value
     *
     * @return JsonResponse
     */
    public function create(CreateAccountInputValue $value)
    {
        $existingAccount = $this->accountRepository->findOneBy(['userId' => $value->getUserId()]);
        if (null !== $existingAccount) {
            throw new BadRequestHttpException('Account already exists');
        }

        $account = new Account();
        $account->setUserId($value->getUserId());
        $account->setAmount(0);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $this->createJsonResponse($account, Response::HTTP_CREATED);
    }

    /**
     * @Route("/account/{userId}", name="account_show", methods="GET", requirements={"userId"="\d+"})
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function show(int $userId)
    {
        $account = $this->accountRepository->findOneBy(['userId' => $userId]);
        if (null === $account) {
            throw new NotFoundHttpException();
        }

        return $this->createJsonResponse($account);
    }

    /**
     * @Route("/account/{userId}/insert", name="account_insert", methods="POST", requirements={"userId"="\d+"})
     *
     * @param int $userId
     * @param AccountWithdrawInputValue $value
     *
     * @return JsonResponse
     */
    public function insert(int $userId, AccountWithdrawInputValue $value)
    {
        $i = 0;
        while ($i < 3) {
            $account = $this->accountRepository->findOneBy(['userId' => $userId]);
            if (null === $account) {
                if ($i === 2) {
                    throw new NotFoundHttpException();
                } else {
                    usleep(100*1000);
                }
            }
            $i++;
        }

        $account->setAmount($account->getAmount() + $value->getAmount());
        $this->entityManager->flush();

        return $this->createJsonResponse($account);
    }

    /**
     * @Route("/account/{userId}/withdraw", name="account_withdraw", methods="POST", requirements={"userId"="\d+"})
     *
     * @param int $userId
     * @param AccountWithdrawInputValue $value
     *
     * @return JsonResponse
     */
    public function withdraw(int $userId, AccountWithdrawInputValue $value)
    {
        $account = $this->accountRepository->findOneBy(['userId' => $userId]);
        if (null === $account) {
            throw new NotFoundHttpException();
        }

        $availableAmount = $account->getAmount();
        if ($value->getAmount() > $availableAmount) {
            throw new BadRequestHttpException(sprintf('Not enough amount, only %f available', $availableAmount), null, 1000);
        }
        $account->setAmount($availableAmount - $value->getAmount());
        $this->entityManager->flush();

        return $this->createJsonResponse($account);
    }

    /**
     * @Route("/account/{userId}", name="account_delete", methods="DELETE", requirements={"userId"="\d+"})
     *
     * @param int $userId
     * @return Response
     */
    public function delete(int $userId)
    {
        $account = $this->accountRepository->findOneBy(['userId' => $userId]);
        if (null !== $account) {
            $this->entityManager->remove($account);
            $this->entityManager->flush();
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/account/me", name="account_me", methods={"GET"})
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

        return $this->forward('App\Controller\AccountController::show', [
            'userId' => (int) $userId,
        ]);
    }

    /**
     * @Route("/account/me/insert", name="account_me_insert", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function meInsert(Request $request)
    {
        $userId = $request->headers->get('X-UserId');
        if (null === $userId) {
            return $this->createJsonResponse('Not authenticated');
        }

        return $this->forward('App\Controller\AccountController::insert', [
            'userId' => (int) $userId,
        ]);
    }
}
