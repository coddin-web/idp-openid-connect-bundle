<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRefreshToken;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;

interface OAuthRefreshTokenRepository
{
    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByExternalId(string $externalId): OAuthRefreshToken;

    public function revoke(OAuthRefreshToken $refreshToken): void;
}
