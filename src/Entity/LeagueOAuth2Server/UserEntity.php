<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\LeagueOAuth2Server;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;

final class UserEntity implements UserEntityInterface, ClaimSetInterface
{
    public function __construct(
        private readonly User $user,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getClaims(): array
    {
        return [
            'nickname' => $this->user->getUsername(),
            'profile' => $this->user->getUsername(),
            'updated_at' => $this->user->getUpdatedAt()->format(\DateTimeInterface::ISO8601),
            'email' => $this->user->getEmail(),
            // @todo make dynamic? (verified / checked user)
            'email_verified' => true,
            'nonce' => '',
        ];
    }

    public function getIdentifier(): string
    {
        return $this->user->getUuid();
    }
}
