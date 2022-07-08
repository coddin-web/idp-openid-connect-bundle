<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

use Assert\Assertion;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;

final class OAuthAuthorizationCodeCollection
{
    /** @param array<OAuthAuthorizationCode> $oauthAuthorizationCodes */
    private function __construct(
        private readonly array $oauthAuthorizationCodes,
    ) {
    }

    /**
     * @param array<OAuthAuthorizationCode> $oauthAuthorizationCodes
     */
    public static function create(array $oauthAuthorizationCodes): self
    {
        Assertion::allIsInstanceOf($oauthAuthorizationCodes, OAuthAuthorizationCode::class);

        return new self($oauthAuthorizationCodes);
    }

    /**
     * @return array<OAuthAuthorizationCode>
     */
    public function all(): array
    {
        return $this->oauthAuthorizationCodes;
    }
}
