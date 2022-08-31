<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

use Assert\Assertion;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;

/**
 * @template-implements DoctrineEntityCollection<OAuthAccessToken>
 */
final class OAuthAccessTokenCollection implements DoctrineEntityCollection
{
    /** @param array<int, OAuthAccessToken> $oauthAccessTokens */
    private function __construct(
        private readonly array $oauthAccessTokens,
    ) {
    }

    /**
     * @param array<int, OAuthAccessToken> $entities
     */
    public static function create(array $entities): self
    {
        Assertion::allIsInstanceOf($entities, OAuthAccessToken::class);

        return new self($entities);
    }

    /**
     * @return array<int, OAuthAccessToken>
     */
    public function all(): array
    {
        return $this->oauthAccessTokens;
    }
}
