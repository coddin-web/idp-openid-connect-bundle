<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Helper;

use Coddin\IdentityProvider\Helper\OAuthOpenIDConnectDataHelper;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository;
use Defuse\Crypto\Key;
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

    private readonly string $encryptionKey;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->identityRepository = $this->createMock(IdentityRepository::class);
        $this->encryptionKey = 'def000005eaf651068786e45ce205d1fca7ef2888af744772df31d9f3ff80275e514a667ac3d4306afd4948e8785c2255e210d30a07f3237a8d15145d7565926224b2519';
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
            projectDir: __DIR__ . '/../../../../..',
            encryptionKey: $this->encryptionKey,
            publicKeyPath: 'public/key/path',
            privateKeyPath: 'config/openidconnect/keys/private.key',
            jwkJsonPath: 'jwk/json/path',
            filesystem: $this->filesystem,
            identityRepository: $this->identityRepository,
        );

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(
            expected: Key::class,
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
            expected: __DIR__ . '/../../../../../public/key/path',
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
