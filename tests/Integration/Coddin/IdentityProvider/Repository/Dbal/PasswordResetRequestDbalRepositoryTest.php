<?php

declare(strict_types=1);

namespace Tests\Integration\Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\DataFixtures\Data\PasswordResetRequest;
use Coddin\IdentityProvider\DataFixtures\Data\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\PasswordResetRequest as PasswordResetRequestEntity;
use Coddin\IdentityProvider\Exception\PasswordResetEntityNotFoundException;
use Coddin\IdentityProvider\Repository\Dbal\PasswordResetRequestDbalRepository;
use Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\Dbal\PasswordResetRequestDbalRepository
 * @covers ::__construct
 */
final class PasswordResetRequestDbalRepositoryTest extends KernelTestCase
{
    private readonly PasswordResetRequestDbalRepository $repository;
    private readonly UserDbalRepository $userRepository;
    private readonly EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /* @phpstan-ignore-next-line */
        $this->repository = $kernel->getContainer()
            ->get(PasswordResetRequestDbalRepository::class);
        /* @phpstan-ignore-next-line */
        $this->userRepository = $kernel->getContainer()
            ->get(UserDbalRepository::class);
        /* @phpstan-ignore-next-line */
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @test
     * @covers ::getValidPasswordResetRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function get_valid_passwordResetRequest(): void
    {
        /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\User $user */
        $user = $this->userRepository->findOneByUsername(User::UserName->value);

        $passwordResetRequest = $this->repository->getValidPasswordResetRequest(
            user: $user,
            token: PasswordResetRequest::Token->value,
        );

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(
            PasswordResetRequestEntity::class,
            $passwordResetRequest,
        );
    }

    /**
     * @test
     * @covers ::getValidPasswordResetRequest
     */
    public function cant_find_valid_passwordResetRequest(): void
    {
        /** @var \Coddin\IdentityProvider\Entity\OpenIDConnect\User $user */
        $user = $this->userRepository->findOneByUsername(User::UserName->value);

        self::expectException(PasswordResetEntityNotFoundException::class);
        self::expectExceptionMessage('The password reset request could not be found');

        $this->repository->getValidPasswordResetRequest(
            user: $user,
            token: PasswordResetRequest::InvalidToken->value,
        );
    }

    /**
     * @test
     * @covers ::getUserForResetToken
     */
    public function get_user_for_token_unknown_token(): void
    {
        self::expectException(PasswordResetEntityNotFoundException::class);
        self::expectExceptionMessage('The password reset request could not be found');

        $this->repository->getUserForResetToken('non_existing_token');
    }

    /**
     * @test
     * @covers ::getUserForResetToken
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function get_user_for_token(): void
    {
        $passwordResetRequestUser = $this->repository->getUserForResetToken(PasswordResetRequest::Token->value);

        self::assertEquals(User::UserName->value, $passwordResetRequestUser->getUsername());
    }

    /**
     * @test
     * @covers ::invalidateTokenForUser
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function invalidate_token_for_user(): void
    {
        /** @var PasswordResetRequestEntity $passwordResetRequest */
        $passwordResetRequest = $this->repository->findOneBy(['token' => PasswordResetRequest::Token->value]);
        $passwordResetRequestUser = $this->repository->getUserForResetToken(PasswordResetRequest::Token->value);

        self::assertEquals(User::UserName->value, $passwordResetRequestUser->getUsername());
        self::assertNull($passwordResetRequest->getUsedAt());

        $this->repository->invalidateTokenForUser(
            user: $passwordResetRequestUser,
            token: PasswordResetRequest::Token->value,
        );

        $this->entityManager->refresh($passwordResetRequest);

        $this->repository->getUserForResetToken(PasswordResetRequest::Token->value);

        /** @var PasswordResetRequestEntity $passwordResetRequest */
        $passwordResetRequest = $this->repository->findOneBy(['token' => PasswordResetRequest::Token->value]);

        self::assertNotNull($passwordResetRequest->getUsedAt());
    }
}
