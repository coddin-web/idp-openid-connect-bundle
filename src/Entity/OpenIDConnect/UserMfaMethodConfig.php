<?php

/**
 * @noinspection PhpPropertyCanBeReadonlyInspection
 * @noinspection PhpPropertyOnlyWrittenInspection
 */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

class UserMfaMethodConfig
{
    // @phpstan-ignore-next-line
    private int $id;

    public function __construct(
        private string $key,
        private string $value,
        private UserMfaMethod $userMfaMethod,
    ) {
    }

    // Internal properties.
    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    // Relations.
    public function getUserMfaMethod(): UserMfaMethod
    {
        return $this->userMfaMethod;
    }
}
