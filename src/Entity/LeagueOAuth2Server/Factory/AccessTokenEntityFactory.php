<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\LeagueOAuth2Server\Factory;

use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\AccessTokenEntity;

/**
 * @codeCoverageIgnore
 */
final class AccessTokenEntityFactory
{
    public function create(): AccessTokenEntity
    {
        return new AccessTokenEntity();
    }
}
