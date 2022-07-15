<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Coddin\IdentityProvider\Request\Validation\RequestDtoValidator;
use Coddin\IdentityProvider\Request\Validation\RequestObjectDtoInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\Validation\RequestDtoValidator
 */
final class RequestDtoValidatorTest extends TestCase
{
    /** @var MockObject & ValidatorInterface $validator */
    private $validator;
    /** @var MockObject & RequestObjectDtoInterface $requestObject */
    private $requestObject;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->requestObject = $this->createMock(RequestObjectDtoInterface::class);
    }

    /**
     * @test
     * @covers ::validate
     */
    public function validate(): void
    {
        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($this->requestObject)
            ->willReturn(
                new ConstraintViolationList([
                    new ConstraintViolation(
                        'This is an error',
                        'This is an error',
                        [],
                        null,
                        null,
                        'ErrorValue',
                    ),
                ]),
            );

        self::expectException(RequestConstraintException::class);
        self::expectExceptionMessage('A constraint violation has occurred');

        RequestDtoValidator::validate(
            validator: $this->validator,
            dto: $this->requestObject,
        );
    }
}
