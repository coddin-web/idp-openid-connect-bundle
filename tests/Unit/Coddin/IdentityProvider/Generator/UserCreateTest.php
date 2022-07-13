<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\OpenIDConnect\Domain\Generator;

use Coddin\IdentityProvider\Generator\UserCreate;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Generator\UserCreate
 */
final class UserCreateTest extends TestCase
{
    /**
     * @test
     * @noinspection PhpUnhandledExceptionInspection
     * @covers ::create
     */
    public function create_the_user(): void
    {
        $roles = ['ROLE_ADMIN'];
        $password = 'thisisaverysecurepassword';

        $user = UserCreate::create(
            username: 'username',
            email: 'user@name.test',
            password: $password,
            roles: $roles,
        );

        $checkRoles = $roles;
        $checkRoles[] = 'ROLE_USER';

        self::assertEquals(
            $checkRoles,
            $user->getRoles(),
        );
        self::assertTrue(
            \password_verify($password, $user->getPassword()),
        );
        self::assertEquals(
            36,
            \mb_strlen($user->getUuid()),
        );
    }
}
