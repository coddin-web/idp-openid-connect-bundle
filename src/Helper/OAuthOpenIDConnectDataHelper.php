<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Helper;

use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository;
use League\OAuth2\Server\CryptKey;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use Safe\Exceptions\FilesystemException;

final class OAuthOpenIDConnectDataHelper implements OAuthOpenIDConnectDataHelperInterface
{
    public function __construct(
        private readonly string $projectDir,
        private readonly IdentityRepository $identityRepository,
    ) {
    }

    public function encryptionKey(): string
    {
        // TODO: This should be a dev key, production should override with ENV var.
        return 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen';
    }

    public function privateKeyCryptKey(): CryptKey
    {
        return new CryptKey($this->privateKeyPath(), null, false);
    }

    public function privateKeyPath(): string
    {
        // TODO: This should be a dev key, production should override with ENV var.
        return $this->projectDir . '/config/openidconnect/keys/private.key';
    }

    public function publicKeyPath(): string
    {
        // TODO: This should be a dev key, production should override with ENV var.
        return $this->projectDir . '/config/openidconnect/keys/public.key';
    }

    /**
     * @throws FilesystemException
     */
    public function getJsonWebKeysFromConfig(): string
    {
        // TODO: This should be dev data, production should override with ENV var.
        return \Safe\file_get_contents($this->projectDir . '/config/openidconnect/jwks.json');
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
