<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\OpenIDConnect\Domain\Collection;

use Assert\InvalidArgumentException;
use Coddin\IdentityProvider\Collection\OAuthAccessTokenCollection;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Collection\OAuthAccessTokenCollection
 * @covers ::__construct
 */
final class OAuthAccessTokenCollectionTest extends TestCase
{
    /**
     * @test
     * @covers ::all
     * @covers ::create
     */
    public function create_the_collection(): void
    {
        $accessTokens = [];
        $accessTokensCollection = OAuthAccessTokenCollection::create($accessTokens);

        self::assertEmpty($accessTokensCollection->all());

        self::expectException(InvalidArgumentException::class);

        $notAccessToken = new \stdClass();
        /* @phpstan-ignore-next-line */
        OAuthAccessTokenCollection::create([$notAccessToken]);
    }
}
