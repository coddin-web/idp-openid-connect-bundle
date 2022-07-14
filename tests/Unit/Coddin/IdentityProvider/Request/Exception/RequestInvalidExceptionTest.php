<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Exception;

use Coddin\IdentityProvider\Request\Exception\RequestInvalidException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\Exception\RequestInvalidException
 */
final class RequestInvalidExceptionTest extends TestCase
{
    /**
     * @test
     * @covers ::createForDenormalization
     */
    public function create_for_denormalization(): void
    {
        $exception = RequestInvalidException::createForDenormalization('message');

        self::assertEquals(400, $exception->getStatusCode());
        self::assertEquals('message', $exception->getMessage());
    }

    /**
     * @test
     * @covers ::createFromConstraintViolations
     */
    public function create_from_constraint_violations(): void
    {
        $constraintViolations = new ConstraintViolationList([
            new ConstraintViolation(
                'This is an error',
                'This is an error',
                [],
                null,
                null,
                'ErrorValue',
            ),
        ]);
        $errors = array_values((array) $constraintViolations)[0];

        $exception = RequestInvalidException::createFromConstraintViolations(...$errors);

        self::assertEquals(409, $exception->getStatusCode());
        self::assertEquals('Constraint violations found: This is an error', $exception->getMessage());
    }
}
