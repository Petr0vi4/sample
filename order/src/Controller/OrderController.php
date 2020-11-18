<?php

namespace App\Controller;

use App\Controller\InputValue\CreateOrderInputValue;
use App\Entity\Order;
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
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        $userId = (int) $userId;

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

        $this->messageBus->dispatch(new NotifyAboutOrderCreated($order->getId()));
        $this->entityManager->flush();

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
