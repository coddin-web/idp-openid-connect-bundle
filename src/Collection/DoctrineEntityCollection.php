<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

/**
 * @template T
 */
interface DoctrineEntityCollection
{
    /**
     * @param array<int, T> $entities
     * @return static
     */
    public static function create(array $entities): self;

    /**
     * @return array<int, T>
     */
    public function all(): array;
}
