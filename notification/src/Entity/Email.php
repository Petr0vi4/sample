<?php

namespace App\Entity;

use App\Repository\EmailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmailRepository::class)
 */
class Email
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fromAddress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $toAddress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $messageBody;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromAddress(): ?string
    {
        return $this->fromAddress;
    }

    public function setFromAddress(string $fromAddress): self
    {
        $this->fromAddress = $fromAddress;

        return $this;
    }

    public function getToAddress(): ?string
    {
        return $this->toAddress;
    }

    public function setToAddress(string $toAddress): self
    {
        $this->toAddress = $toAddress;

        return $this;
    }

    public function getMessageBody(): ?string
    {
        return $this->messageBody;
    }

    public function setMessageBody(string $messageBody): self
    {
        $this->messageBody = $messageBody;

        return $this;
    }

    public function getSent(): ?bool
    {
        return $this->sent;
    }

    public function setSent(bool $sent): self
    {
        $this->sent = $sent;

        return $this;
    }
}
