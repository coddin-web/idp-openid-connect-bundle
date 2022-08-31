<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository\Dbal;

use Coddin\IdentityProvider\Collection\MfaMethodCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaMethod as MfaMethodIdentifier;
use Coddin\IdentityProvider\Entity\OpenIDConnect\MfaMethod;
use Coddin\IdentityProvider\Repository\MfaMethodRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MfaMethod>
 */
final class MfaMethodDbalRepository extends ServiceEntityRepository implements MfaMethodRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, MfaMethod::class);
    }

    public function getAll(): MfaMethodCollection
    {
        return MfaMethodCollection::create($this->findAll());
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getByIdentifier(MfaMethodIdentifier $mfaMethod): MfaMethod
    {
        $mfaMethod = $this->findOneBy([
            'identifier' => $mfaMethod->value,
        ]);

        if ($mfaMethod === null) {
            throw new EntityNotFoundException('Unknown MfaMethod type');
        }

        return $mfaMethod;
    }
}
