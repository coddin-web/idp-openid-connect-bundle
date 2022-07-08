<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ScopeEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

final class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getScopeEntityByIdentifier($identifier): ScopeEntityInterface
    {
        // TODO: Make configurable.
        $scopes = [
            'openid' => [
                'description' => 'Enable OpenID Connect support',
            ],
            'profile' => [
                'description' => 'Profile information',
            ],
            'email' => [
                'description' => 'The user\'s email',
            ],
            'nonce_scope' => [
                'description' => 'A `hack` to get the jumbojett/openid-client library to work',
            ],
        ];

        if (\array_key_exists($identifier, $scopes) === false) {
            throw new \LogicException('Provided scope does not exist');
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($identifier);

        return $scope;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore  Remove this tag when the method changes and logic is
     *                      added to programmatically edit the scopes
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null,
    ): array {
        /**
         * It is possible to programmatically modify the scopes of the access token,
         * this can be done in this method, e.g.
         *
         * if ((int) $userIdentifier === 1) {
         *     $scope = new ScopeEntity();
         *     $scope->setIdentifier('email')
         *     $scopes[] = $scope;
         * }
         */

        return $scopes;
    }
}
