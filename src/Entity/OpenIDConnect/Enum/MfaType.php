<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect\Enum;

enum MfaType: string
{
    case TYPE_TOTP = 'totp';
    case TYPE_U2F = 'u2f';
}
