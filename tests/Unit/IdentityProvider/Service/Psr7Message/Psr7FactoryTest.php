<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityProvider\Service\Psr7Message;

use Coddin\IdentityProvider\Service\Psr7Message\Psr7Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

final class Psr7FactoryTest extends TestCase
{
    /**
     * @test
     * @covers \Coddin\IdentityProvider\Service\Psr7Message\Psr7Factory::create
     */
    public function create(): void
    {
        // @phpstan-ignore-next-line
        self::assertInstanceOf(PsrHttpFactory::class, Psr7Factory::create());
    }
}
