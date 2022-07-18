<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Validation\Exception;

use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException
 */
final class RequestConstraintExceptionTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::create
     */
    public function create(): void
    {
        $requestConstraintException = RequestConstraintException::create(
            constraintSubject: UserRegistration::class,
            constraintViolationList: new ConstraintViolationList([]),
        );

        self::assertEquals(
            'A constraint violation has occurred',
            $requestConstraintException->getMessage(),
        );
    }
}
