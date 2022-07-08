<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

/**
 * @codeCoverageIgnore Models should NOT be fat, hence we can ignore them
 */
class OAuthRedirectUri
{
    public function __construct(
        private readonly int $id,
        private readonly string $uri,
        private readonly OAuthClient $oAuthClient,
    ) {
    }

    // Internal properties.
    public function getId(): int
    {
        return $this->id;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    // Relations.
    public function getOAuthClient(): OAuthClient
    {
        return $this->oAuthClient;
    }
}
