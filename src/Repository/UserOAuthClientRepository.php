<?php

declare(strict_types=1);

namespace Coddin\OpenIDConnect\Domain\Repository;

use Coddin\IdentityProvider\Entity\OpenIDConnect\UserOAuthClient;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;

interface UserOAuthClientRepository
{
    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByUserReferenceAndExternalId(string $userReference, string $externalId): UserOAuthClient;
}
