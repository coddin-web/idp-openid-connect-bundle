<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Exception;

use Doctrine\ORM\EntityNotFoundException;

final class UserMfaMethodNotFoundException extends EntityNotFoundException
{
    public static function create(string $message): self
    {
        return new self($message);
    }

    public static function activeNotFound(): self
    {
        return new self('There was no active UserMfaMethod found (that is also validated)');
    }
}
