<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Message;

/**
 * @codeCoverageIgnore
 */
final class ResetPassword
{
    private function __construct(
        private readonly string $email,
        private readonly string $locale,
    ) {
    }

    public static function create(
        string $email,
        string $locale = 'en',
    ): self {
        return new self($email, $locale);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
