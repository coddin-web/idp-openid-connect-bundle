<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DataFixtures\Data;

enum PasswordResetRequest: string
{
    case Token = '6f7c85ee7bb7cd387f9cda8da42f797117669bdc2811e2fbc5fae88663b3ad307ef6e5665fe4aaa6';
    case InvalidToken = '22c0fc48433878dcbd82b0384146b75ed7ac3ded6af53958631bc798f36bd0efff18b0519ed11448';
}
