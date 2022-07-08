<?php

/**
 * @noinspection PhpPropertyCanBeReadonlyInspection
 * @noinspection PhpPropertyOnlyWrittenInspection
 */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

use Doctrine\Common\Collections\Collection;

/**
 * @codeCoverageIgnore Models should NOT be fat, hence we can ignore them
 */
class OAuthAccessToken
{
    // @phpstan-ignore-next-line
    private int $id;

    /**
     * @param null|Collection<int, OAuthRefreshToken> $oAuthRefreshTokens
     */
    public function __construct(
        private string $externalId,
        private \DateTimeImmutable $expiresAt,
        private User $user,
        private OAuthClient $oAuthClient,
        private ?\DateTimeImmutable $revokedAt = null,
        private ?Collection $oAuthRefreshTokens = null,
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

    /**
     * @return Collection<int, OAuthRefreshToken>|null
     */
    public function getOAuthRefreshTokens(): ?Collection
    {
        return $this->oAuthRefreshTokens;
    }
}
