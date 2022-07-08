<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DataFixtures\Data;

enum OAuthRefreshToken: string
{
    case EXTERNAL_ID = 'external_id';
}
