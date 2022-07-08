<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Collection\OAuthClientCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\OpenIDConnect\Domain\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuthClient>
 */
final class OAuthClientDbalRepository extends ServiceEntityRepository implements OAuthClientRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, OAuthClient::class);
    }

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function getOneByExternalId(string $externalId): OAuthClient
    {
        /** @var OAuthClient|null $oAuthClient */
        $oAuthClient = $this->findOneBy(['externalId' => $externalId]);

        if (!$oAuthClient instanceof OAuthClient) {
            throw OAuthEntityNotFoundException::fromClassNameAndExternalId(OAuthClient::class, $externalId);
        }

        return $oAuthClient;
    }

    public function getAll(): OAuthClientCollection
    {
        $oauthClients = $this->findAll();

        return OAuthClientCollection::create($oauthClients);
    }
}
