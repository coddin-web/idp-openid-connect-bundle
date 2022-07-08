<?php

declare(strict_types=1);

namespace Coddin\OpenIDConnect\Domain\Repository;

use Coddin\IdentityProvider\Collection\OAuthClientCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;

interface OAuthClientRepository
{
    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByExternalId(string $externalId): OAuthClient;

    public function getAll(): OAuthClientCollection;
}
