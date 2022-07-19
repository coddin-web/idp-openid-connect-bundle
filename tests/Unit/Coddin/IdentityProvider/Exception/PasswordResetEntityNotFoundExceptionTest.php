<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Exception;

use Coddin\IdentityProvider\Exception\PasswordResetEntityNotFoundException;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Exception\PasswordResetEntityNotFoundException
 */
final class PasswordResetEntityNotFoundExceptionTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create(): void
    {
        $exception = PasswordResetEntityNotFoundException::create();

        self::assertEquals('The password reset request could not be found', $exception->getMessage());
        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(EntityNotFoundException::class, $exception);
    }
}
