<?php

/**
 * @noinspection PhpMissingFieldTypeInspection
 * @noinspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository
 * @covers ::__construct
 */
final class IdentityRepositoryTest extends TestCase
{
    /** @var MockObject & UserRepository */
    private $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
    }

    /**
     * @test
     * @covers ::getUserEntityByIdentifier
     */
    public function get_user_entity_by_identifier_not_string(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('User identifier should be a string; The OAuth library does not use strict typing, hence the `mixed` property');

        $identityRepository = new IdentityRepository($this->userRepository);
        /** @noinspection PhpUnhandledExceptionInspection */
        $identityRepository->getUserEntityByIdentifier(null);
    }

    /**
     * @test
     * @covers ::getUserEntityByIdentifier
     */
    public function get_user_entity_by_identifier(): void
    {
        $identityRepository = new IdentityRepository($this->userRepository);
        /** @noinspection PhpUnhandledExceptionInspection */
        $userEntity = $identityRepository->getUserEntityByIdentifier('identifier');

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(\Coddin\IdentityProvider\Entity\LeagueOAuth2Server\UserEntity::class, $userEntity);
    }
}
