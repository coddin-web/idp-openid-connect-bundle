<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Request\ResetPasswordRequest;
use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\ValidationDataResolver;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\Validation\ValidationDataResolver
 */
final class ValidationDataResolverTest extends TestCase
{
    /**
     * @test
     * @covers ::resolve
     * @covers ::intersectKeys
     */
    public function unknown_request_event(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Unsupported request encountered');

        $validationDataResolver = new ValidationDataResolver();
        $validationDataResolver->resolve(
            type: 'UnknownEvent',
            data: [],
        );
    }

    /**
     * @param class-string $requestEvent
     * @param array<string, string> $data
     * @param array<string, string> $expectedResult
     *
     * @test
     * @covers ::resolve
     * @covers ::intersectKeys
     * @dataProvider data
     */
    public function resolve(
        string $requestEvent,
        array $data,
        array $expectedResult,
    ): void {
        $validationDataResolver = new ValidationDataResolver();
        $result = $validationDataResolver->resolve(
            type: $requestEvent,
            data: $data,
        );

        self::assertEquals($expectedResult, $result);
    }

    /**
     * @return array<int, array<int, array<string, string>|string>>
     */
    public function data(): array
    {
        return [
            [
                UserRegistration::class,
                [
                    'username' => 'user@name',
                    'password' => '$ecr3t',
                    'password_repeat' => '$ecr3t',
                ],
                [
                    'username' => 'user@name',
                    'password' => '$ecr3t',
                    'password_repeat' => '$ecr3t',
                ],
            ],
            [
                UserRegistration::class,
                [
                    'username' => 'user@name',
                    'password' => '$ecr3t',
                    'password_repeat' => '$ecr3t',
                    'foo' => 'bar',
                    'bar' => 'foo',
                ],
                [
                    'username' => 'user@name',
                    'password' => '$ecr3t',
                    'password_repeat' => '$ecr3t',
                ],
            ],
            [
                UserRegistration::class,
                [
                    'username' => 'user@name',
                    'password' => '$ecr3t',
                ],
                [
                    'username' => 'user@name',
                    'password' => '$ecr3t',
                ],
            ],
            [
                ResetPasswordRequest::class,
                [
                    'reset_csrf_token' => 'reset_csrf_token',
                    'reset_token' => 'reset_token',
                    'password' => '$ecr3t',
                    'password_repeat' => '$ecr3t',
                ],
                [
                    'reset_csrf_token' => 'reset_csrf_token',
                    'reset_token' => 'reset_token',
                    'password' => '$ecr3t',
                    'password_repeat' => '$ecr3t',
                ],
            ],
        ];
    }
}
