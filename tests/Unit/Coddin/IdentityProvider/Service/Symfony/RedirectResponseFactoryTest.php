<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Service\Symfony;

use Coddin\IdentityProvider\Service\Symfony\RedirectResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Service\Symfony\RedirectResponseFactory
 */
final class RedirectResponseFactoryTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create(): void
    {
        $redirectResponseFactory = new RedirectResponseFactory();
        $redirectResponse = $redirectResponseFactory->create(
            url: 'https://foo.bar',
            status: 301,
            headers: [
                'X-Custom-Header' => 'This is a custom header',
            ],
        );

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(RedirectResponse::class, $redirectResponse);
        self::assertEquals(301, $redirectResponse->getStatusCode());
        self::assertTrue(
            $redirectResponse->headers->has('X-Custom-Header'),
        );
        self::assertEquals(
            'This is a custom header',
            $redirectResponse->headers->get('X-Custom-Header'),
        );
    }
}
