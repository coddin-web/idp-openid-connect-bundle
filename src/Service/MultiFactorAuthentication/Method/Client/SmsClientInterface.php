<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Client;

interface SmsClientInterface
{
    public function sendTextMessage(
        string $originator,
        string $recipient,
        string $body,
    ): void;
}
