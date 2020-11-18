<?php

declare(strict_types=1);

namespace App\Dto;

class Account
{
    private $id;

    private $userId;

    private $amount;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUsername(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setEmail(float $amount): void
    {
        $this->amount = $amount;
    }
}
