<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\Psr7Message;

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

final class Psr7Factory
{
    public static function create(): PsrHttpFactory
    {
        $psr17Factory = new Psr17Factory();
        return new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    }
}
