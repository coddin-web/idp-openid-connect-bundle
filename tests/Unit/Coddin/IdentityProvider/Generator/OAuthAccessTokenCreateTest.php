<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\OpenIDConnect\Domain\Generator;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\OAuthAccessTokenCreate;
use Tests\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Generator\OAuthAccessTokenCreate
 */
final class OAuthAccessTokenCreateTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create_the_access_token(): void
    {
        $externalId = '1234-5678-90ab';
        $expiresAt = new \DateTimeImmutable();

        $user = $this->createMock(User::class);
        $oauthClient = $this->createMock(OAuthClient::class);

        $oauthAccessTokenCreate = new OAuthAccessTokenCreate();
        $oauthAccessToken = $oauthAccessTokenCreate->create(
            externalId: $externalId,
            expiresAt: $expiresAt,
            user: $user,
            oAuthClient: $oauthClient,
        );

        self::assertVariableEqualsGetMethod(
            $oauthAccessToken,
            [
                'externalId' => $externalId,
                'expiresAt' => $expiresAt,
            ],
        );
    }
}
