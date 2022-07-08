<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

/**
 * @codeCoverageIgnore Models should NOT be fat, hence we can ignore them
 */
class UserOAuthClient
{
    // @phpstan-ignore-next-line
    private int $id;

    public function __construct(
        private readonly User $user,
        private readonly OAuthClient $oAuthClient,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOAuthClient(): OAuthClient
    {
        return $this->oAuthClient;
    }
}
