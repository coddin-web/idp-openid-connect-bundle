<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DataFixtures\Data;

enum User: string
{
    case UserName = 'username';
    case Email = 'username@email.test';
    case Password = 'pa$$w0rd';
}
