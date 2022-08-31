<?php

/**
 * @noinspection PhpPropertyCanBeReadonlyInspection
 * @noinspection PhpPropertyOnlyWrittenInspection
 */

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\OpenIDConnect;

use Doctrine\Common\Collections\Collection;
use Safe\Exceptions\JsonException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @codeCoverageIgnore Models should NOT be fat, hence we can ignore them
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // @phpstan-ignore-next-line
    private int $id;

    /**
     * @param Collection<int, OAuthAuthorizationCode> $oAuthAuthorizationCodes
     * @param Collection<int, OAuthAccessToken> $oAuthAccessTokens
     * @param Collection<int, UserOAuthClient> $userOAuthClients
     * @param Collection<int, UserMfaMethod> $userMfaMethods
     */
    public function __construct(
        private string $uuid,
        private string $username,
        private string $email,
        private string $password,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private Collection $oAuthAuthorizationCodes,
        private Collection $oAuthAccessTokens,
        private Collection $userOAuthClients,
        private Collection $userMfaMethods,
        private string $roles,
    ) {
    }

    // Internal properties.
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Relations.

    /**
     * @return Collection<int, OAuthAuthorizationCode>
     */
    public function getOAuthAuthorizationCodes(): Collection
    {
        return $this->oAuthAuthorizationCodes;
    }

    /**
     * @return Collection<int, OAuthAccessToken>
     */
    public function getOAuthAccessTokens(): Collection
    {
        return $this->oAuthAccessTokens;
    }

    /**
     * @return Collection<int, UserOAuthClient>
     */
    public function getUserOAuthClients(): Collection
    {
        return $this->userOAuthClients;
    }

    /**
     * @return Collection<int, UserMfaMethod>
     */
    public function getUserMfaMethods(): Collection
    {
        return $this->userMfaMethods;
    }

    // Symfony Security.

    /**
     * @return array<int, string>
     * @throws JsonException
     */
    public function getRoles(): array
    {
        $roles = \Safe\json_decode($this->roles);

        if (!\is_array($roles)) {
            throw new \LogicException('Roles were incorrectly saved to the database and could not be decoded');
        }

        // Guarantee every user at least has ROLE_USER.
        $roles[] = 'ROLE_USER';

        return \array_unique($roles);
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // Not needed.
    }

    public function getUserIdentifier(): string
    {
        return $this->uuid;
    }
}
