<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection;

use Assert\Assertion;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthAuthorizationCode;

/**
 * @template-implements DoctrineEntityCollection<OAuthAuthorizationCode>
 */
final class OAuthAuthorizationCodeCollection implements DoctrineEntityCollection
{
    /** @param array<OAuthAuthorizationCode> $oauthAuthorizationCodes */
    private function __construct(
        private readonly array $oauthAuthorizationCodes,
    ) {
    }

    /**
     * @param array<int, OAuthAuthorizationCode> $entities
     */
    public static function create(array $entities): self
    {
        Assertion::allIsInstanceOf($entities, OAuthAuthorizationCode::class);

        return new self($entities);
    }

    /**
     * @return array<int, OAuthAuthorizationCode>
     */
    public function all(): array
    {
        return $this->oauthAuthorizationCodes;
    }
}
