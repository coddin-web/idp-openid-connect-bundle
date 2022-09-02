<?php

declare(strict_types=1);

namespace Tests\Helper;

use Coddin\IdentityProvider\Service\MultiFactorAuthentication\Method\Client\SmsClientInterface;
use Psr\Log\LoggerInterface;

final class NullSmsClient implements SmsClientInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function sendTextMessage(
        string $originator,
        string $recipient,
        string $body,
    ): void {
        $this->logger->debug(
            sprintf(
                'An SMS has been sent for MFA: [originator: `%s`, recipient: `%s`, body: `%s`]',
                $originator,
                $recipient,
                $body,
            ),
        );
    }
}
