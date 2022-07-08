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
class OAuthRefreshToken
{
    // @phpstan-ignore-next-line
    private int $id;

    public function __construct(
        private string $externalId,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $expiresAt,
        private OAuthAccessToken $oAuthAccessToken,
        private ?\DateTimeImmutable $revokedAt = null,
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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
    public function getAccessToken(): OAuthAccessToken
    {
        return $this->oAuthAccessToken;
    }
}
