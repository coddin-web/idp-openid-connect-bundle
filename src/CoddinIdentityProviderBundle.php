<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider;

use Coddin\IdentityProvider\DependencyInjection\CoddinIdentityProviderExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class CoddinIdentityProviderBundle extends AbstractBundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        // Todo: Figure out why the ...Extension is not auto loaded.
        return new CoddinIdentityProviderExtension();
    }
}
