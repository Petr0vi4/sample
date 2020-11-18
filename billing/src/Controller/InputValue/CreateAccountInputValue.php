<?php

declare(strict_types=1);

namespace App\Controller\InputValue;

use App\Controller\InputValue;
use Symfony\Component\Validator\Constraints as Assert;

class CreateAccountInputValue implements InputValue
{
    /**
     * @Assert\NotBlank()
     *
     * @var int
     */
    private $userId;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}
