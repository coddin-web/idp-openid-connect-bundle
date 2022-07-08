<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\OpenIDConnect;

use Safe\Exceptions\JsonException;

use function Safe\json_encode;
use function Safe\json_decode;

/**
 * TODO: Make this expandable / configurable.
 */
final class ConfigGenerator
{
    private const CONFIG_BASE_DATA = [
        'issuer' => 'https://{{IDP_HOST}}/identity',
        'authorization_endpoint' => 'https://{{IDP_HOST}}/identity/authorize',
        'token_endpoint' => 'https://{{IDP_HOST}}/identity/token',
        'end_session_endpoint' => 'https://{{IDP_HOST}}/identity/endsession',
        'introspection_endpoint' => 'https://{{IDP_HOST}}/identity/introspect',
        'jwks_uri' => 'https://{{IDP_HOST}}/identity/.well-known/jwks',
        'grant_types_supported' => [
            'authorization_code',
            'refresh_token',
        ],
        'id_token_signing_alg_values_supported' => [
            'RS256',
        ],
        'response_modes_supported' => [
            'query',
        ],
        'response_types_supported' => [
            'code',
        ],
        'token_endpoint_auth_methods_supported' => [
            'client_secret_post',
        ],
        'subject_types_supported' => [
            'public',
        ],
        'scopes_supported' => [
            'openid',
            'profile',
            'user',
        ],
        'claims_supported' => [
            'sub',
            'name',
            'updated_at',
        ],
        'code_challenge_methods_supported' => [
            'plain',
            'S256',
        ],
    ];

    /** @var array<string, mixed> */
    private array $wellKnownConfig;

    /**
     * @codeCoverageIgnore
     * @param array<string, mixed> $wellKnownConfig
     */
    private function __construct(array $wellKnownConfig)
    {
        $this->wellKnownConfig = $wellKnownConfig;
    }

    /**
     * @throws JsonException
     */
    public static function create(string $host): self
    {
        $data = str_replace('{{IDP_HOST}}', $host, json_encode(self::CONFIG_BASE_DATA));

        return new self((array) json_decode($data, true));
    }

    /**
     * @throws JsonException
     */
    public function asJson(): string
    {
        return json_encode($this->wellKnownConfig);
    }

    /**
     * @return array<string, mixed>
     */
    public function asArray(): array
    {
        return $this->wellKnownConfig;
    }
}
