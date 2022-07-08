<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

use DateTimeImmutable;

/**
 * @codeCoverageIgnore Models should NOT be fat, hence we can ignore them
 */
class OAuthAuthorizationCode
{
    // @phpstan-ignore-next-line
    private int $id;

    public function __construct(
        private readonly string $externalId,
        private readonly string $redirectUri,
        private readonly DateTimeImmutable $expiresAt,
        private readonly User $user,
        private readonly OAuthClient $oAuthClient,
        private readonly ?DateTimeImmutable $revokedAt = null,
    ) {
    }

    // Internal properties.
    public function getId(): int
    {
        return $this->id;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    // Relations.
    public function getUser(): User
    {
        return $this->user;
    }

    public function getOAuthClient(): OAuthClient
    {
        return $this->oAuthClient;
    }
}
