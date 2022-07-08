<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DataFixtures\Data;

enum OAuthAccessToken: string
{
    case ExternalID = 'external_id';
}
