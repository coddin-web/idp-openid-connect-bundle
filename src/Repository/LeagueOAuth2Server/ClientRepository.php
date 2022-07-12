<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use Coddin\IdentityProvider\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

final class ClientRepository implements ClientRepositoryInterface
{
    public function __construct(
        private readonly OAuthClientRepository $oAuthClientRepository,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getClientEntity($clientIdentifier): ?ClientEntity
    {
        try {
            $oauthClient = $this->oAuthClientRepository->getOneByExternalId($clientIdentifier);
        } catch (OAuthEntityNotFoundException) {
            return null;
        }

        $client = new ClientEntity();

        $client->setIdentifier($clientIdentifier);
        $client->setName($oauthClient->getName());
        $client->setRedirectUri($oauthClient->getRedirectUris()->toArray());
        $client->setConfidential($oauthClient->isConfidential());
        $client->setIsPkce($oauthClient->isPkce());

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        try {
            $oAuthClient = $this->oAuthClientRepository->getOneByExternalId($clientIdentifier);
        } catch (OAuthEntityNotFoundException $e) {
            return false;
        }

        if (!$oAuthClient->isPkce()) {
            if (!is_string($clientSecret)) {
                return false;
            }

            if (
                $oAuthClient->isConfidential() === true
                && \password_verify($clientSecret, $oAuthClient->getSecret()) === false
            ) {
                return false;
            }
        }

        return true;
    }
}
