<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

use Assert\Assertion;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;

/**
 * @template-implements DoctrineEntityCollection<OAuthClient>
 */
final class OAuthClientCollection implements DoctrineEntityCollection
{
    /** @param array<OAuthClient> $oauthClients */
    private function __construct(
        private readonly array $oauthClients,
    ) {
    }

    public static function create(array $entities): self
    {
        Assertion::allIsInstanceOf($entities, OAuthClient::class);

        return new self($entities);
    }

    /**
     * @return array<int, OAuthClient>
     */
    public function all(): array
    {
        return $this->oauthClients;
    }

    public function count(): int
    {
        return \count($this->oauthClients);
    }
}
