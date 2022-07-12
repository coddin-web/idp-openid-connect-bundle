<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityProvider\Entity\OpenIDConnect;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Entity\LeagueOAuth2Server\UserEntity
 * @covers ::__construct
 */
final class UserEntityTest extends TestCase
{
    /**
     * @test
     * @covers ::getClaims
     */
    public function get_claims(): void
    {
        $date = new \DateTimeImmutable();
        $user = $this->createMock(User::class);
        $user
            ->expects(self::exactly(2))
            ->method('getUsername')
            ->willReturn('username');
        $user
            ->expects(self::once())
            ->method('getUpdatedAt')
            ->willReturn($date);
        $user
            ->expects(self::once())
            ->method('getEmail')
            ->willReturn('test@test.nl');

        $userEntity = new \Coddin\IdentityProvider\Entity\LeagueOAuth2Server\UserEntity($user);

        self::assertEquals(
            [
                'nickname' => 'username',
                'profile' => 'username',
                'updated_at' => $date->format(\DateTimeInterface::ISO8601),
                'email' => 'test@test.nl',
                'email_verified' => true,
                'nonce' => '',
            ],
            $userEntity->getClaims(),
        );
    }

    /**
     * @test
     * @covers ::getIdentifier
     */
    public function get_identifier(): void
    {
        $user = $this->createMock(User::class);
        $user
            ->expects(self::once())
            ->method('getUuid')
            ->willReturn('user_uuid');

        $userEntity = new \Coddin\IdentityProvider\Entity\LeagueOAuth2Server\UserEntity($user);

        self::assertEquals('user_uuid', $userEntity->getIdentifier());
    }
}
