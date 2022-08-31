<?php

/**
 * @noinspection PhpPropertyCanBeReadonlyInspection
 * @noinspection PhpPropertyOnlyWrittenInspection
 */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

use Doctrine\Common\Collections\Collection;

class MfaMethod
{
    // @phpstan-ignore-next-line
    private int $id;

    /**
     * @param Collection<int, UserMfaMethod> $userMfaMethods
     */
    public function __construct(
        private string $identifier,
        private string $type,
        private Collection $userMfaMethods,
    ) {
    }

    // Internal properties.
    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    // Relations.

    /**
     * @return Collection<int, UserMfaMethod>
     */
    public function getUserMfaMethods(): Collection
    {
        return $this->userMfaMethods;
    }
}
