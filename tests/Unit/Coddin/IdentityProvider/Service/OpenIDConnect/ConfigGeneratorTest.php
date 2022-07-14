<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Service\OpenIDConnect;

use Coddin\IdentityProvider\Service\OpenIDConnect\ConfigGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Service\OpenIDConnect\ConfigGenerator
 */
final class ConfigGeneratorTest extends TestCase
{
    /**
     * @test
     * @covers ::asArray
     * @covers ::asJson
     * @covers ::create
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function create_and_verify_output(): void
    {
        $configGenerator = ConfigGenerator::create('thisisahost.test');

        self::assertStringContainsString(
            'https:\/\/thisisahost.test\/identity',
            $configGenerator->asJson(),
        );
        self::assertEquals(
            'https://thisisahost.test/identity',
            $configGenerator->asArray()['issuer'],
        );
    }
}
