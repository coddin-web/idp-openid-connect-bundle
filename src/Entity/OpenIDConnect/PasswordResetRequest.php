<?php

/**
 * @noinspection PhpPropertyCanBeReadonlyInspection
 * @noinspection PhpPropertyOnlyWrittenInspection
 */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

/**
 * @codeCoverageIgnore Models should NOT be fat, hence we can ignore them
 */
class PasswordResetRequest
{
    // @phpstan-ignore-next-line
    private int $id;

    public function __construct(
        private User $user,
        private string $token,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $validUntil,
        private ?\DateTimeInterface $usedAt = null,
    ) {
    }

    // Internal properties.
    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getValidUntil(): \DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function getUsedAt(): ?\DateTimeInterface
    {
        return $this->usedAt;
    }

    // Relations.
    public function getUser(): User
    {
        return $this->user;
    }
}
