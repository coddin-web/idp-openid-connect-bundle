<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Exception;

interface RequestExceptionInterface
{
    public function getStatusCode(): int;
}
