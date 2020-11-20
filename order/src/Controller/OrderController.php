<?php

namespace App\Controller;

use App\Controller\InputValue\CreateOrderInputValue;
use App\Controller\InputValue\GetOrderListInputValue;
use App\Entity\Order;
use App\Message\Envelope;
use App\Message\NotifyAboutOrderCreated;
use App\Repository\OrderRepository;
use App\ServiceClient\BillingServiceClient;
use App\ServiceClient\BillingServiceClient\NotEnoughAmountException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $messageBus;
    /**
     * @var BillingServiceClient
     */
    private BillingServiceClient $billingServiceClient;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager, OrderRepository $orderRepository, MessageBusInterface $messageBus, BillingServiceClient $billingServiceClient)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->messageBus = $messageBus;
        $this->billingServiceClient = $billingServiceClient;
    }

    /**
     * @Route("/order", name="order_index", methods="GET")
     *
     * @param Request $request
     * @return array|JsonResponse
     */
    public function index(Request $request)
    {
        $userId = $request->headers->get('X-UserId');
        if (null === $userId) {
            return $this->createJsonResponse('Not authenticated');
        }
        $limit = (int) $request->query->get('limit', 10);
        $offset = (int) $request->query->get('offset', 0);

        $criteria = ['userId' => $userId];
        $orders = $this->orderRepository->findBy($criteria, ['id' => 'DESC'], $limit, $offset);
        $totalCount = $this->orderRepository->count($criteria);
        $version = $this->orderRepository->version($userId);

        return [
            'list' => $orders,
            'totalCount' => $totalCount,
            'version' => $version,
        ];
    }

    /**
     * @Route("/order", name="order_create", methods="POST")
     *
     * @param Request $request
     * @param CreateOrderInputValue $value
     *
     * @return JsonResponse
     */
    public function create(Request $request, CreateOrderInputValue $value)
    {
        $userId = $request->headers->get('X-UserId');
        if (null === $userId) {
            return $this->createJsonResponse('Not authenticated');
        }
        $userVersion = $request->headers->Get('X-Version');
        if (null === $userVersion) {
            throw new BadRequestHttpException('X-Version header required');
        }
        $userId = (int) $userId;
        $version = $this->orderRepository->version($userId);
        if ($version !== $userVersion) {
            throw new ConflictHttpException('Wrong X-Version');
        }

        $order = $this->entityManager->transactional(function () use ($userId, $value) {
            $order = new Order();
            $order->setUserId($userId);
            $order->setAmount($value->getAmount());
            $order->setStatus('new');
            $this->entityManager->persist($order);

            try {
                $this->billingServiceClient->withdraw($userId, $value->getAmount());
                $order->setStatus('paid');
            } catch (NotEnoughAmountException $notEnoughAmountException) {
                $order->setStatus('error');
            }

            $this->messageBus->dispatch(new Envelope(new NotifyAboutOrderCreated($order->getId())));
            $this->entityManager->flush();

            return $order;
        });

        return $this->createJsonResponse($order, Response::HTTP_CREATED);
    }

    /**
     * @Route("/order/{id}", name="order_show", methods="GET", requirements={"id"="\d+"})
     * @ParamConverter("order", class="App\Entity\Order")
     *
     * @param Order $order
     *
     * @return JsonResponse
     */
    public function show(Order $order)
    {
        return $this->createJsonResponse($order);
    }
}
