<?php

declare(strict_types=1);

namespace App\Controller\InputValue;

use App\Controller\InputValue;
use Symfony\Component\Validator\Constraints as Assert;

class LoginInputValue implements InputValue
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $username;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $password;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
