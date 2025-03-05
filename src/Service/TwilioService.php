<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $client;
    private $twilioPhoneNumber;

    public function __construct(string $accountSid, string $authToken, string $twilioPhoneNumber)
    {
        $this->client = new Client($accountSid, $authToken);
        $this->twilioPhoneNumber = $twilioPhoneNumber;
    }

    public function sendSms(string $to, string $message): void
    {
        $this->client->messages->create(
            $to,
            [
                'from' => $this->twilioPhoneNumber,
                'body' => $message,
            ]
        );
    }
}