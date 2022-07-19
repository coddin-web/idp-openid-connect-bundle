<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Exception;

use Doctrine\ORM\EntityNotFoundException;

final class PasswordResetEntityNotFoundException extends EntityNotFoundException
{
    public static function create(): self
    {
        return new self('The password reset request could not be found');
    }
}
