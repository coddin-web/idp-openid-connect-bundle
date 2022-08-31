<?php

/**
 * @noinspection PhpPropertyCanBeReadonlyInspection
 * @noinspection PhpPropertyOnlyWrittenInspection
 */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

use Doctrine\Common\Collections\Collection;

class UserMfaMethod
{
    // @phpstan-ignore-next-line
    private int $id;

    /**
     * @param Collection<int, UserMfaMethodConfig> $userMfaMethodConfigs
     */
    public function __construct(
        private bool $isActive,
        private bool $isValidated,
        private User $user,
        private MfaMethod $mfaMethod,
        private Collection $userMfaMethodConfigs,
    ) {
    }

    // Internal properties.
    public function getId(): int
    {
        return $this->id;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    // Relations.
    public function getUser(): User
    {
        return $this->user;
    }

    public function getMfaMethod(): MfaMethod
    {
        return $this->mfaMethod;
    }

    /**
     * @return Collection<int, UserMfaMethodConfig>
     */
    public function getUserMfaMethodConfigs(): Collection
    {
        return $this->userMfaMethodConfigs;
    }
}
