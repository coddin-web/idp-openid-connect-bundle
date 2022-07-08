<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Doctrine\Common\Collections\ArrayCollection;

final class OAuthClientCreate
{
    public static function create(
        string $externalId,
        string $externalIdReadable,
        string $name,
        string $displayName,
        string $secret,
        bool $isConfidential = true,
        bool $isPkce = true,
        ?string $creationWebhookUrl = null,
    ): OAuthClient {
        return new OAuthClient(
            externalId: $externalId,
            externalIdReadable: $externalIdReadable,
            name: $name,
            displayName: $displayName,
            isConfidential: $isConfidential,
            isPkce: $isPkce,
            secret: $secret,
            creationWebhookUrl: $creationWebhookUrl,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
            redirectUris: new ArrayCollection(),
            oAuthAuthorizationCodes: new ArrayCollection(),
            oAuthAccessTokens: new ArrayCollection(),
            userOAuthClients: new ArrayCollection(),
        );
    }
}
