<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityProvider\Entity\OpenIDConnect;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRedirectUri;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity
 */
final class ClientEntityTest extends TestCase
{
    /**
     * @test
     * @covers ::setRedirectUri
     */
    public function set_redirectUri_from_entity(): void
    {
        $clientEntity = new ClientEntity();
        $oauthRedirectUri = $this->createMock(OAuthRedirectUri::class);
        $oauthRedirectUri
            ->expects(self::once())
            ->method('getUri')
            ->willReturn('uri.test');

        $clientEntity->setRedirectUri($oauthRedirectUri);

        self::assertEquals('uri.test', $clientEntity->getRedirectUri());
    }

    /**
     * @test
     * @covers ::setRedirectUri
     */
    public function set_redirectUri_from_array_singular(): void
    {
        $clientEntity = new ClientEntity();
        $oauthRedirectUri = $this->createMock(OAuthRedirectUri::class);
        $oauthRedirectUri
            ->expects(self::once())
            ->method('getUri')
            ->willReturn('uri.test');

        $clientEntity->setRedirectUri([$oauthRedirectUri]);

        self::assertEquals('uri.test', $clientEntity->getRedirectUri());
    }

    /**
     * @test
     * @covers ::setRedirectUri
     */
    public function set_redirectUri_from_array_multiple(): void
    {
        $clientEntity = new ClientEntity();
        $oauthRedirectUri = $this->createMock(OAuthRedirectUri::class);
        $oauthRedirectUri
            ->expects(self::once())
            ->method('getUri')
            ->willReturn('uri.test');
        $oauthRedirectUriOther = $this->createMock(OAuthRedirectUri::class);
        $oauthRedirectUriOther
            ->expects(self::once())
            ->method('getUri')
            ->willReturn('uri2.test');

        $clientEntity->setRedirectUri([$oauthRedirectUri, $oauthRedirectUriOther]);

        self::assertIsArray($clientEntity->getRedirectUri());
        self::assertEquals('uri.test', $clientEntity->getRedirectUri()[0]);
        self::assertEquals('uri2.test', $clientEntity->getRedirectUri()[1]);
    }
}
