<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Request\ResetPasswordRequest;
use Coddin\IdentityProvider\Request\UserRegistration;

final class ValidationDataResolver
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function resolve(
        string $type,
        array $data,
    ): array {
        return match ($type) {
            UserRegistration::class => $this->intersectKeys($data, ['username', 'password', 'password_repeat']),
            ResetPasswordRequest::class => $this->intersectKeys($data, ['reset_csrf_token', 'reset_token', 'password', 'password_repeat']),
            default => throw new \LogicException('Unsupported request encountered'),
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string> $intersection
     * @return array<string, mixed>
     */
    private function intersectKeys(
        array $data,
        array $intersection,
    ): array {
        return \array_intersect_key(
            $data,
            \array_flip($intersection),
        );
    }
}
