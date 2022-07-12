<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Collection\OAuthAccessTokenCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;

interface OAuthAccessTokenRepository
{
    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByExternalId(string $externalId): OAuthAccessToken;

    public function findAllActiveForUser(User $user): OAuthAccessTokenCollection;

    public function revoke(OAuthAccessToken $accessToken): void;

    public function revokeAllActiveForUser(User $user): void;
}
