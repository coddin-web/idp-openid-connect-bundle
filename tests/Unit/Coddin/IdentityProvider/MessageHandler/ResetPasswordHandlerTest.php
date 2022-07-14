<?php

/**
 * @noinspection PhpMissingFieldTypeInspection
 * @noinspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\MessageHandler;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Message\ResetPassword;
use Coddin\IdentityProvider\MessageHandler\ResetPasswordHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\MessageHandler\ResetPasswordHandler
 */
final class ResetPasswordHandlerTest extends TestCase
{
    /** @var MailerInterface & MockObject */
    private $mailer;
    /** @var TranslatorInterface & MockObject */
    private $translator;
    /** @var UserRepository & MockObject */
    private $userRepository;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
    }

    /**
     * @test
     * @covers ::__invoke
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function no_user(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('findOneByUsername')
            ->with('user@mail.nu')
            ->willReturn(null);

        $this->mailer
            ->expects(self::never())
            ->method('send');

        $resetPassword = ResetPassword::create('user@mail.nu');

        $resetPasswordHandler = $this->createResetPasswordHandler();
        $resetPasswordHandler($resetPassword);
    }

    /**
     * @test
     * @covers ::__invoke
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function email_sent(): void
    {
        $username = 'user@mail.nu';
        $user = $this->createMock(User::class);
        $user
            ->expects(self::once())
            ->method('getUsername')
            ->willReturn($username);

        $this->userRepository
            ->expects(self::once())
            ->method('findOneByUsername')
            ->with($username)
            ->willReturn($user);

        $this->translator
            ->expects(self::exactly(2))
            ->method('trans')
            ->withConsecutive(
                ['email.reset_password.subject', [], null, 'en'],
                ['email.reset_password.salutation', ['username' => $username], null, 'en'],
            );

        $this->mailer
            ->expects(self::once())
            ->method('send');

        $resetPassword = ResetPassword::create($username);

        $resetPasswordHandler = $this->createResetPasswordHandler();
        $resetPasswordHandler($resetPassword);
    }

    private function createResetPasswordHandler(): ResetPasswordHandler
    {
        return new ResetPasswordHandler(
            mailer: $this->mailer,
            translator: $this->translator,
            userRepository: $this->userRepository,
        );
    }
}
