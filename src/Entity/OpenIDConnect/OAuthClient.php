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
class OAuthClient
{
    // @phpstan-ignore-next-line
    private int $id;

    /**
     * @param Collection<int, OAuthRedirectUri> $redirectUris
     * @param Collection<int, OAuthAuthorizationCode> $oAuthAuthorizationCodes
     * @param Collection<int, OAuthAccessToken> $oAuthAccessTokens
     * @param Collection<int, UserOAuthClient> $userOAuthClients
     */
    public function __construct(
        private string $externalId,
        private string $externalIdReadable,
        private string $name,
        private string $displayName,
        private bool $isConfidential,
        private bool $isPkce,
        private string $secret,
        private ?string $creationWebhookUrl,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private Collection $redirectUris,
        private Collection $oAuthAuthorizationCodes,
        private Collection $oAuthAccessTokens,
        private Collection $userOAuthClients,
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

    public function getExternalIdReadable(): string
    {
        return $this->externalIdReadable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function isConfidential(): bool
    {
        return $this->isConfidential;
    }

    public function isPkce(): bool
    {
        return $this->isPkce;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getCreationWebhookUrl(): ?string
    {
        return $this->creationWebhookUrl;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Relations.

    /**
     * @return Collection<int, OAuthRedirectUri>
     */
    public function getRedirectUris(): Collection
    {
        return $this->redirectUris;
    }

    /**
     * @return Collection<int, OAuthAuthorizationCode>
     */
    public function getOAuthAuthorizationCodes(): Collection
    {
        return $this->oAuthAuthorizationCodes;
    }

    /**
     * @return Collection<int, OAuthAccessToken>
     */
    public function getOAuthAccessTokens(): Collection
    {
        return $this->oAuthAccessTokens;
    }

    /**
     * @return Collection<int, UserOAuthClient>
     */
    public function getUserOAuthClients(): Collection
    {
        return $this->userOAuthClients;
    }
}
