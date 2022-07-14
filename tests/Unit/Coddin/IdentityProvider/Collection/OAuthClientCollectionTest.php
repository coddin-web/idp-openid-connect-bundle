<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Collection;

use Assert\InvalidArgumentException;
use Coddin\IdentityProvider\Collection\OAuthClientCollection;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Collection\OAuthClientCollection
 * @covers ::__construct
 */
final class OAuthClientCollectionTest extends TestCase
{
    /**
     * @test
     * @covers ::all
     * @covers ::count
     * @covers ::create
     */
    public function create_the_collection(): void
    {
        $oauthClients = [];
        $oauthClientCollection = OAuthClientCollection::create($oauthClients);

        self::assertEmpty($oauthClientCollection->all());
        self::assertEquals(0, $oauthClientCollection->count());

        self::expectException(InvalidArgumentException::class);

        $notOAuthClient = new \stdClass();
        /* @phpstan-ignore-next-line */
        OAuthClientCollection::create([$notOAuthClient]);
    }
}
