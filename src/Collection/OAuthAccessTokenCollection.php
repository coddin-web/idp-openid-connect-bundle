<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

use Assert\Assertion;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAccessToken;

final class OAuthAccessTokenCollection
{
    /** @param array<OAuthAccessToken> $oauthAccessTokens */
    private function __construct(
        private readonly array $oauthAccessTokens,
    ) {
    }

    /**
     * @param array<OAuthAccessToken> $oauthAccessTokens
     */
    public static function create(array $oauthAccessTokens): self
    {
        Assertion::allIsInstanceOf($oauthAccessTokens, OAuthAccessToken::class);

        return new self($oauthAccessTokens);
    }

    /**
     * @return array<OAuthAccessToken>
     */
    public function all(): array
    {
        return $this->oauthAccessTokens;
    }
}
