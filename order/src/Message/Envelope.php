<?php

declare(strict_types=1);

namespace App\Message;

class Envelope
{
    private $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
