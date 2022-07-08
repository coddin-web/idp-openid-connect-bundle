<?php

declare(strict_types=1);

namespace Coddin\OpenIDConnect\Domain\Repository;

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
