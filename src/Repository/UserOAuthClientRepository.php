<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserOAuthClient;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;

interface UserOAuthClientRepository
{
    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByUserReferenceAndExternalId(string $userReference, string $externalId): UserOAuthClient;
}
