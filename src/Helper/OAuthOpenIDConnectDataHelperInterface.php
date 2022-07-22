<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Helper;

use Defuse\Crypto\Key;
use League\OAuth2\Server\CryptKey;
use OpenIDConnectServer\IdTokenResponse;
use Safe\Exceptions\FilesystemException;

interface OAuthOpenIDConnectDataHelperInterface
{
    public function encryptionKey(): Key;

    public function privateKeyCryptKey(): CryptKey;

    public function privateKeyPath(): string;

    public function publicKeyPath(): string;

    /**
     * @throws FilesystemException
     */
    public function getJsonWebKeysFromConfig(): string;

    public function getResponseType(): IdTokenResponse;
}
