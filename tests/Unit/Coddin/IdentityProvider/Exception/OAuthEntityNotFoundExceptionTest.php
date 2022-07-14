<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Exception;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException
 * @covers ::createMessage
 */
final class OAuthEntityNotFoundExceptionTest extends TestCase
{
    /**
     * @test
     * @covers ::fromClassNameAndId
     */
    public function create_from_className_and_Id(): void
    {
        $exception = OAuthEntityNotFoundException::fromClassNameAndId(
            className: OAuthClient::class,
            id: 1,
        );

        self::assertEquals(
            expected: 'OAuthEntity of type `Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient` with id `1` was not found',
            actual: $exception->getMessage(),
        );
    }

    /**
     * @test
     * @covers ::fromClassNameAndUuid
     */
    public function create_from_className_and_Uuid(): void
    {
        $exception = OAuthEntityNotFoundException::fromClassNameAndUuid(
            className: OAuthClient::class,
            uuid: 'this_is_a_uuid',
        );

        self::assertEquals(
            expected: 'OAuthEntity of type `Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient` with uuid `this_is_a_uuid` was not found',
            actual: $exception->getMessage(),
        );
    }

    /**
     * @test
     * @covers ::fromClassNameAndExternalId
     */
    public function create_from_className_and_externalId(): void
    {
        $exception = OAuthEntityNotFoundException::fromClassNameAndExternalId(
            className: OAuthClient::class,
            externalId: 'external_id',
        );

        self::assertEquals(
            expected: 'OAuthEntity of type `Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient` with externalId `external_id` was not found',
            actual: $exception->getMessage(),
        );
    }
}
