<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Collection\MultiFactorAuthentication;

use Coddin\IdentityProvider\Collection\MfaMethodCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\MfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Doctrine\Common\Collections\Collection;

final class AccountMfaMethodCollection
{
    /**
     * @param array<AccountMfaMethod> $accountMfaMethods
     */
    public function __construct(
        private readonly array $accountMfaMethods,
    ) {
    }

    public static function create(
        MfaMethodCollection $mfaMethods,
        User $user,
    ): self {
        $accountMfaMethods = [];
        $userMfaMethods = $user->getUserMfaMethods();
        foreach ($mfaMethods->all() as $mfaMethod) {
            $userConfiguredMfaMethod = null;
            $userHasConfigured = self::userHasMfaMethodConfigured($mfaMethod, $userMfaMethods);
            if ($userHasConfigured) {
                $userConfiguredMfaMethod = self::getUserConfiguredMfaMethod($mfaMethod, $userMfaMethods);
            }

            $accountMfaMethods[] = new AccountMfaMethod(
                name: $mfaMethod->getIdentifier(),
                userHasConfigured: $userHasConfigured,
                isActiveForUser: $userHasConfigured && $userConfiguredMfaMethod->isActive(),
                userMfaMethodId: $userConfiguredMfaMethod?->getId(),
            );
        }

        return new self($accountMfaMethods);
    }

    /**
     * @return array<AccountMfaMethod>
     */
    public function all(): array
    {
        return $this->accountMfaMethods;
    }

    /**
     * @param Collection<int, UserMfaMethod> $userMfaMethods
     */
    private static function userHasMfaMethodConfigured(
        MfaMethod $mfaMethod,
        Collection $userMfaMethods,
    ): bool {
        return \count(
            \array_filter(
                $userMfaMethods->toArray(),
                function (UserMfaMethod $userMfaMethod) use ($mfaMethod) {
                    return $mfaMethod->getIdentifier() === $userMfaMethod->getMfaMethod()->getIdentifier() && $userMfaMethod->isValidated();
                },
            ),
        ) === 1;
    }

    /**
     * @param Collection<int, UserMfaMethod> $userMfaMethods
     */
    private static function getUserConfiguredMfaMethod(
        MfaMethod $mfaMethod,
        Collection $userMfaMethods,
    ): UserMfaMethod {
        $userMfaMethods = \array_filter(
            $userMfaMethods->toArray(),
            function (UserMfaMethod $userMfaMethod) use ($mfaMethod) {
                return $mfaMethod->getIdentifier() === $userMfaMethod->getMfaMethod()->getIdentifier();
            },
        );

        if (\count($userMfaMethods) > 1) {
            throw new \LogicException('It should not be possible to have multiple mfaMethod matches for the User');
        }

        if (\count($userMfaMethods) === 0) {
            throw new \LogicException('It is expected to have a configured UserMfaMethod here');
        }

        return $userMfaMethods[0];
    }
}
