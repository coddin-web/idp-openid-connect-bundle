<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection\MultiFactorAuthentication;

final class AccountMfaMethod
{
    public function __construct(
        private readonly string $name,
        private readonly bool $userHasConfigured = false,
        private readonly bool $isActiveForUser = false,
        private readonly ?int $userMfaMethodId = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function userHasConfigured(): bool
    {
        return $this->userHasConfigured;
    }

    public function isActiveForUser(): bool
    {
        return $this->isActiveForUser;
    }

    public function getUserMfaMethodId(): ?int
    {
        return $this->userMfaMethodId;
    }
}
