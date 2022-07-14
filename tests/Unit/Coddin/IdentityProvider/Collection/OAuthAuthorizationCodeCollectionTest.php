<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Collection;

use Assert\InvalidArgumentException;
use Coddin\IdentityProvider\Collection\OAuthAuthorizationCodeCollection;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Collection\OAuthAuthorizationCodeCollection
 * @covers ::__construct
 */
final class OAuthAuthorizationCodeCollectionTest extends TestCase
{
    /**
     * @test
     * @covers ::all
     * @covers ::create
     */
    public function create_the_collection(): void
    {
        $authorizationCodes = [];
        $authAuthorizationCodeCollection = OAuthAuthorizationCodeCollection::create($authorizationCodes);

        self::assertEmpty($authAuthorizationCodeCollection->all());

        self::expectException(InvalidArgumentException::class);

        $notAuthorizationCode = new \stdClass();
        /* @phpstan-ignore-next-line */
        OAuthAuthorizationCodeCollection::create([$notAuthorizationCode]);
    }
}
