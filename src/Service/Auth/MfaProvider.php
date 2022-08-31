<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\Auth;

use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Repository\MfaMethodRepository;
use Coddin\IdentityProvider\Repository\UserMfaMethodConfigRepository;
use Coddin\IdentityProvider\Repository\UserMfaMethodRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final class MfaProvider
{
    private const SESSION_KEY = 'oidc_idp_mfa_verified';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly MfaMethodRepository $mfaMethodRepository,
        private readonly UserMfaMethodRepository $userMfaMethodRepository,
        private readonly UserMfaMethodConfigRepository $userMfaMethodConfigRepository,
    ) {
    }

    public function hasActiveMfa(User $user): bool
    {
        foreach ($user->getUserMfaMethods() as $userMfaMethod) {
            if ($userMfaMethod->isActive() && $userMfaMethod->isValidated()) {
                return true;
            }
        }

        return false;
    }

    public function isVerified(): bool
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, false) === true;
    }

    public function setMfaVerified(): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, true);
    }

    /**
     * @todo No more array usage.
     * @param array<string, string> $mfaMethodConfigurations
     */
    public function mfaMethodRegistration(
        User $user,
        MfaMethod $mfaMethod,
        array $mfaMethodConfigurations,
    ): void {
        $this->userMfaMethodRepository->removeAllStaleMethodsByType(
            user: $user,
            mfaMethod: $mfaMethod,
        );

        $mfaMethodEntity = $this->mfaMethodRepository->getByIdentifier($mfaMethod);
        $userMfaMethod = $this->userMfaMethodRepository->initialize($user, $mfaMethodEntity);

        foreach ($mfaMethodConfigurations as $key => $value) {
            $this->userMfaMethodConfigRepository->initialize(
                userMfaMethod: $userMfaMethod,
                configKey: $key,
                configValue: $value,
            );
        }
    }
}
