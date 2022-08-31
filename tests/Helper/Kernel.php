<?php

declare(strict_types=1);

namespace Tests\Helper;

use Coddin\IdentityProvider\CoddinIdentityProviderBundle;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
    use MicroKernelTrait {
        registerContainerConfiguration as originalRegisterContainerConfiguration;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new DoctrineBundle(),
            new DoctrineFixturesBundle(),
            new DAMADoctrineTestBundle(),
            new CoddinIdentityProviderBundle(),
            new TwigBundle(),
        ];
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension(
                extension: 'framework',
                values: [
                    'secret' => '%env(APP_SECRET)%',
                ],
            );
        });

        $this->originalRegisterContainerConfiguration($loader);
    }
}
