<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserOAuthClient;

final class UserOAuthClientCreate
{
    public static function create(
        User $user,
        OAuthClient $oauthClient,
    ): UserOAuthClient {
        return new UserOAuthClient(
            user: $user,
            oAuthClient: $oauthClient,
        );
    }
}
