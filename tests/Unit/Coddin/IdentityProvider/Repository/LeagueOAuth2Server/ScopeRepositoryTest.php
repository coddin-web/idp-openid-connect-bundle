<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpMissingFieldTypeInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Repository\LeagueOAuth2Server;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ScopeRepository
 */
final class ScopeRepositoryTest extends TestCase
{
    /**
     * @test
     * @covers ::getScopeEntityByIdentifier
     */
    public function get_scope_entity_by_identifier_not_supported_scope(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Provided scope does not exist');

        $scopeRepo = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ScopeRepository();
        $scopeRepo->getScopeEntityByIdentifier('foo.bar');
    }

    /**
     * @test
     * @covers ::getScopeEntityByIdentifier
     */
    public function get_scope_entity_by_identifier(): void
    {
        $scopeRepo = new \Coddin\IdentityProvider\Repository\LeagueOAuth2Server\ScopeRepository();
        $scope = $scopeRepo->getScopeEntityByIdentifier('openid');

        self::assertEquals('openid', $scope->getIdentifier());
    }
}
