<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

use Assert\Assertion;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;

final class OAuthClientCollection
{
    /** @param array<OAuthClient> $oauthClients */
    private function __construct(
        private readonly array $oauthClients,
    ) {
    }

    /**
     * @param array<OAuthClient> $oauthClients
     */
    public static function create(array $oauthClients): self
    {
        Assertion::allIsInstanceOf($oauthClients, OAuthClient::class);

        return new self($oauthClients);
    }

    /**
     * @return array<OAuthClient>
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
