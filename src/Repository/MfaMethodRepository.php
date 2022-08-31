<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Repository;

use Coddin\IdentityProvider\Collection\MfaMethodCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaMethod as MfaMethodIdentifier;
use Coddin\IdentityProvider\Entity\OpenIDConnect\MfaMethod;

interface MfaMethodRepository
{
    public function getAll(): MfaMethodCollection;

    public function getByIdentifier(MfaMethodIdentifier $mfaMethod): MfaMethod;
}
