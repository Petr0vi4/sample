<?php

declare(strict_types=1);

namespace App\Message;

class NotifyAboutOrderCreated
{
    private $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
