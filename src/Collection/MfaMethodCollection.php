<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

use Coddin\IdentityProvider\Entity\OpenIDConnect\MfaMethod;

/**
 * @template-implements DoctrineEntityCollection<MfaMethod>
 */
final class MfaMethodCollection implements DoctrineEntityCollection
{
    /**
     * @param array<int, MfaMethod> $mfaMethods
     */
    public function __construct(
        private readonly array $mfaMethods,
    ) {
    }

    public static function create(array $entities): self
    {
        return new self($entities);
    }

    public function all(): array
    {
        return $this->mfaMethods;
    }
}
