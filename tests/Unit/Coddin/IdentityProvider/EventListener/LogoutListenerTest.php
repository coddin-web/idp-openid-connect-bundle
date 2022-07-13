<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\IdentityProvider\EventListener;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Repository\OAuthAccessTokenRepository;
use Coddin\IdentityProvider\Repository\OAuthAuthorizationCodeRepository;
use Coddin\IdentityProvider\EventListener\LogoutListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\EventListener\LogoutListener
 */
final class LogoutListenerTest extends TestCase
{
    /** @var OAuthAccessTokenRepository & MockObject $oauthAccessTokenRepository */
    private $oauthAccessTokenRepository;
    /** @var OAuthAuthorizationCodeRepository & MockObject $oauthAuthorizationCodeRepository */
    private $oauthAuthorizationCodeRepository;

    protected function setUp(): void
    {
        $this->oauthAccessTokenRepository = $this->createMock(OAuthAccessTokenRepository::class);
        $this->oauthAuthorizationCodeRepository = $this->createMock(OAuthAuthorizationCodeRepository::class);
    }

    /**
     * @test
     * @covers ::onSymfonyComponentSecurityHttpEventLogoutEvent
     */
    public function logout_event_missing_token(): void
    {
        $listener = $this->createListener();
        $logoutEvent = $this->createMock(LogoutEvent::class);

        $logoutEvent
            ->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->oauthAccessTokenRepository
            ->expects(self::never())
            ->method('revokeAllActiveForUser');

        $this->oauthAuthorizationCodeRepository
            ->expects(self::never())
            ->method('revokeAllActiveForUser');

        $listener->onSymfonyComponentSecurityHttpEventLogoutEvent($logoutEvent);
    }

    /**
     * @test
     * @covers ::onSymfonyComponentSecurityHttpEventLogoutEvent
     */
    public function logout_event_missing_user(): void
    {
        $listener = $this->createListener();
        $logoutEvent = $this->createMock(LogoutEvent::class);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        $logoutEvent
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->oauthAccessTokenRepository
            ->expects(self::never())
            ->method('revokeAllActiveForUser');

        $this->oauthAuthorizationCodeRepository
            ->expects(self::never())
            ->method('revokeAllActiveForUser');

        $listener->onSymfonyComponentSecurityHttpEventLogoutEvent($logoutEvent);
    }

    /**
     * @test
     * @covers ::onSymfonyComponentSecurityHttpEventLogoutEvent()
     */
    public function handle_logout_event(): void
    {
        $listener = $this->createListener();
        $logoutEvent = $this->createMock(LogoutEvent::class);

        $user = $this->createMock(User::class);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $logoutEvent
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->oauthAccessTokenRepository
            ->expects(self::once())
            ->method('revokeAllActiveForUser')
            ->with($user);

        $this->oauthAuthorizationCodeRepository
            ->expects(self::once())
            ->method('revokeAllActiveForUser')
            ->with($user);

        $listener->onSymfonyComponentSecurityHttpEventLogoutEvent($logoutEvent);
    }

    private function createListener(): LogoutListener
    {
        return new LogoutListener(
            oauthAccessTokenRepository: $this->oauthAccessTokenRepository,
            oauthAuthorizationCodeRepository: $this->oauthAuthorizationCodeRepository,
        );
    }
}
