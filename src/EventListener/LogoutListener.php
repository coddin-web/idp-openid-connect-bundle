<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\EventListener;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\OpenIDConnect\Domain\Repository\OAuthAccessTokenRepository;
use Coddin\OpenIDConnect\Domain\Repository\OAuthAuthorizationCodeRepository;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class LogoutListener
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        private readonly OAuthAccessTokenRepository $oauthAccessTokenRepository,
        private readonly OAuthAuthorizationCodeRepository $oauthAuthorizationCodeRepository,
    ) {
    }

    public function onSymfonyComponentSecurityHttpEventLogoutEvent(
        LogoutEvent $logoutEvent,
    ): void {
        $token = $logoutEvent->getToken();
        if ($token === null) {
            return;
        }

        /** @var User|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return;
        }

        $this->oauthAccessTokenRepository->revokeAllActiveForUser($user);
        $this->oauthAuthorizationCodeRepository->revokeAllActiveForUser($user);
    }
}
