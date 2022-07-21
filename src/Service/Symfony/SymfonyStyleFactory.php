<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SymfonyStyleFactory
{
    public function create(
        InputInterface $input,
        OutputInterface $output,
    ): SymfonyStyle {
        return new SymfonyStyle($input, $output);
    }
}
