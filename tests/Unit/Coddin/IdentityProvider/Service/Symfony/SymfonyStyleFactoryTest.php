<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Service\Symfony;

use Coddin\IdentityProvider\Service\Symfony\SymfonyStyleFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Service\Symfony\SymfonyStyleFactory
 */
final class SymfonyStyleFactoryTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create(): void
    {
        $symfonyStyleFactory = new SymfonyStyleFactory();
        $symfonyStyle = $symfonyStyleFactory->create(
            input: $this->createMock(InputInterface::class),
            output: $this->createMock(OutputInterface::class),
        );

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(
            expected: SymfonyStyle::class,
            actual: $symfonyStyle,
        );
    }
}
