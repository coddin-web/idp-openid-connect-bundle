<?php

declare(strict_types=1);

namespace Coddin\OpenIDConnect\Domain\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;

interface OAuthAuthorizationCodeRepository
{
    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByExternalId(string $externalId): OAuthAuthorizationCode;

    public function revoke(OAuthAuthorizationCode $authorizationCode): void;

    public function revokeAllActiveForUser(User $user): void;
}
