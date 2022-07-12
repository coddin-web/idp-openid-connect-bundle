<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Tests\Unit\IdentityProvider\Service\OpenIDConnect;

use Coddin\IdentityProvider\Service\OpenIDConnect\ConfigGenerator;
use PHPUnit\Framework\TestCase;

final class ConfigGeneratorTest extends TestCase
{
    /**
     * @test
     * @covers \Coddin\IdentityProvider\Service\OpenIDConnect\ConfigGenerator::asArray
     * @covers \Coddin\IdentityProvider\Service\OpenIDConnect\ConfigGenerator::asJson
     * @covers \Coddin\IdentityProvider\Service\OpenIDConnect\ConfigGenerator::create
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
