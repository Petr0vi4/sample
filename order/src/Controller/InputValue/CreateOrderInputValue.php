<?php

declare(strict_types=1);

namespace App\Controller\InputValue;

use App\Controller\InputValue;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderInputValue implements InputValue
{
    /**
     * @Assert\Type("float")
     *
     * @var float
     */
    private $amount;

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
}
