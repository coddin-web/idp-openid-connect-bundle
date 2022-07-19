<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Service\Auth;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Service\Auth\ResetPassword;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Service\Auth\ResetPassword
 * @covers ::__construct
 */
final class ResetPasswordTest extends TestCase
{
    /**
     * @test
     * @covers ::createToken
     * @covers ::generateResetToken
     * @covers ::createValidityDate
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function create_token(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('persist');
        $entityManager
            ->expects(self::once())
            ->method('flush');

        $resetPassword = new ResetPassword($entityManager);

        $token = $resetPassword->createToken($this->createMock(User::class));

        self::assertEquals(80, \strlen($token));
    }
}
