<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Helper;

use Coddin\IdentityProvider\Helper\OAuthOpenIDConnectDataHelper;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository;
use League\Flysystem\Filesystem;
use League\OAuth2\Server\CryptKey;
use OpenIDConnectServer\IdTokenResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Helper\OAuthOpenIDConnectDataHelper
 * @covers ::__construct
 */
final class OAuthOpenIDConnectDataHelperTest extends TestCase
{
    /** @var Filesystem & MockObject */
    private $filesystem;
    /** @var IdentityRepository & MockObject */
    private $identityRepository;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->identityRepository = $this->createMock(IdentityRepository::class);
    }

    /**
     * @test
     * @covers ::encryptionKey
     * @covers ::privateKeyCryptKey
     * @covers ::privateKeyPath
     * @covers ::publicKeyPath
     * @covers ::getJsonWebKeysFromConfig
     * @covers ::getResponseType
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function initialize(): void
    {
        $helper = new OAuthOpenIDConnectDataHelper(
            encryptionKey: 'encryption_key',
            publicKeyPath: 'public/key/path',
            privateKeyPath: __DIR__ . '/../../../../../config/openidconnect/keys/private.key',
            jwkJsonPath: 'jwk/json/path',
            filesystem: $this->filesystem,
            identityRepository: $this->identityRepository,
        );

        self::assertEquals(
            expected: 'encryption_key',
            actual: $helper->encryptionKey(),
        );

        $cryptKey = $helper->privateKeyCryptKey();
        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(
            expected: CryptKey::class,
            actual: $cryptKey,
        );

        self::assertEquals(
            expected: 'file://' . __DIR__ . '/../../../../../config/openidconnect/keys/private.key',
            actual: $cryptKey->getKeyPath(),
        );

        self::assertEquals(
            expected: __DIR__ . '/../../../../../config/openidconnect/keys/private.key',
            actual: $helper->privateKeyPath(),
        );

        self::assertEquals(
            expected: 'public/key/path',
            actual: $helper->publicKeyPath(),
        );

        $this->filesystem
            ->expects(self::once())
            ->method('read')
            ->with('jwk/json/path')
            ->willReturn('raw_json_data');

        self::assertEquals(
            expected: 'raw_json_data',
            actual: $helper->getJsonWebKeysFromConfig(),
        );

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(
            expected: IdTokenResponse::class,
            actual: $helper->getResponseType(),
        );
    }
}
