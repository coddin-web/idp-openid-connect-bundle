<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DataFixtures\Data;

enum OAuthAuthorizationCode: string
{
    case VALID_EXTERNAL_ID = 'authorization_code_external_id';
    case EXPIRED_EXTERNAL_ID = 'authorization_code_external_id_expired';
    case REVOKED_EXTERNAL_ID = 'authorization_code_external_id_revoked';
}
