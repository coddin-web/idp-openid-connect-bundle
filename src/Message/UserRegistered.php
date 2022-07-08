<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Message;

/**
 * @codeCoverageIgnore
 */
final class UserRegistered
{
    public function __construct(
        private readonly int $userId,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
