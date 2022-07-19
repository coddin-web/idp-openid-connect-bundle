<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\PasswordResetRequestCreate;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Generator\PasswordResetRequestCreate
 */
final class PasswordResetRequestCreateTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create_passwordResetRequest(): void
    {
        $user = $this->createMock(User::class);
        $validUntil = new \DateTimeImmutable();

        $passwordResetRequest = PasswordResetRequestCreate::create(
            $user,
            'this_is_a_token',
            $validUntil,
        );

        self::assertEquals('this_is_a_token', $passwordResetRequest->getToken());
        self::assertEquals($validUntil, $passwordResetRequest->getValidUntil());
    }
}
