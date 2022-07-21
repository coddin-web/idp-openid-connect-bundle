<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Helper;

use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\OAuth2\Server\CryptKey;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;

final class OAuthOpenIDConnectDataHelper implements OAuthOpenIDConnectDataHelperInterface
{
    public function __construct(
        private readonly string $encryptionKey,
        private readonly string $publicKeyPath,
        private readonly string $privateKeyPath,
        private readonly string $jwkJsonPath,
        private readonly Filesystem $filesystem,
        private readonly IdentityRepository $identityRepository,
    ) {
    }

    public function encryptionKey(): string
    {
        return $this->encryptionKey;
    }

    public function privateKeyCryptKey(): CryptKey
    {
        return new CryptKey($this->privateKeyPath(), null, false);
    }

    public function privateKeyPath(): string
    {
        return $this->privateKeyPath;
    }

    public function publicKeyPath(): string
    {
        return $this->publicKeyPath;
    }

    /**
     * @throws FilesystemException
     */
    public function getJsonWebKeysFromConfig(): string
    {
        return $this->filesystem->read($this->jwkJsonPath);
    }

    public function getResponseType(): IdTokenResponse
    {
        $claimExtractor = new ClaimExtractor();

        return new IdTokenResponse(
            $this->identityRepository,
            $claimExtractor,
        );
    }
}
