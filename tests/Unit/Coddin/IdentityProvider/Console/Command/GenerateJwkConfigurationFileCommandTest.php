<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Console\Command;

use Coddin\IdentityProvider\Console\Command\GenerateJwkConfigurationFileCommand;
use Coddin\IdentityProvider\Service\Symfony\SymfonyStyleFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCheckFileExistence;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Console\Command\GenerateJwkConfigurationFileCommand
 * @covers ::__construct
 * @covers ::askForPath
 */
final class GenerateJwkConfigurationFileCommandTest extends KernelTestCase
{
    /** @var Filesystem & MockObject */
    private $fileSystem;
    /** @var SymfonyStyleFactory & MockObject */
    private $symfonyStyleFactory;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->fileSystem = $this->createMock(Filesystem::class);
        $this->symfonyStyleFactory = $this->createMock(SymfonyStyleFactory::class);
    }

    /**
     * @test
     * @covers ::execute
     */
    public function missing_path(): void
    {
        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle
            ->expects(self::once())
            ->method('ask')
            ->willReturn(1);

        $this->symfonyStyleFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($symfonyStyle);

        $this->fileSystem
            ->expects(self::never())
            ->method('fileExists');

        $command = $this->createCommand();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('The path should be a string');

        $this->testCommand($command);
    }

    /**
     * @test
     * @covers ::execute
     */
    public function file_system_exception(): void
    {
        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle
            ->expects(self::once())
            ->method('ask')
            ->willReturn('/non/existent/path');

        $this->symfonyStyleFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($symfonyStyle);

        $this->fileSystem
            ->expects(self::once())
            ->method('fileExists')
            ->with('/non/existent/path')
            ->willThrowException(new UnableToCheckFileExistence('This is a message'));

        $symfonyStyle
            ->expects(self::once())
            ->method('error')
            ->with('This is a message');

        $command = $this->createCommand();

        $commandTester = $this->testCommand($command);

        self::assertEquals(1, $commandTester->getStatusCode());
    }

    /**
     * @test
     * @covers ::execute
     */
    public function public_key_not_found(): void
    {
        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle
            ->expects(self::once())
            ->method('ask')
            ->willReturn('/non/existent/path');

        $this->symfonyStyleFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($symfonyStyle);

        $this->fileSystem
            ->expects(self::once())
            ->method('fileExists')
            ->willReturn(false);

        $command = $this->createCommand();

        self::expectException(FileNotFoundException::class);
        self::expectExceptionMessage('The public key could not be found at the given path');

        $this->testCommand($command);
    }

    /**
     * @test
     * @covers ::execute
     * @dataProvider paths
     * phpcs:disable SlevomatCodingStandard.Functions.RequireTrailingCommaInCall.MissingTrailingComma
     * phpcs:disable SlevomatCodingStandard.Files.LineLength.LineTooLong
     * phpcs:disable SlevomatCodingStandard.Functions.RequireTrailingCommaInCall.MissingTrailingComma
     * phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
     */
    public function execute(string $path): void
    {
        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle
            ->expects(self::once())
            ->method('ask')
            ->willReturn($path);

        $this->symfonyStyleFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($symfonyStyle);

        $this->fileSystem
            ->expects(self::once())
            ->method('fileExists')
            ->willReturn(true);

        $this->fileSystem
            ->expects(self::once())
            ->method('read')
            ->willReturn(
<<<EOF
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA68PLgzXPLoc2jRKBBnH6
x9pJ3aquMSKvnVdAwi7J3cQwr0kxRlH60YqRSrxPKkeM1pYGMhMBdzpuO4PItHPG
5y8bPzf9b7gb6V3g/5hem6QjVJyrp+V8QGYkqk8vHwLUyviIRgeTy2Li+1I4DWuc
nJXmcLc3ctkURgVtmFpTRqXgj91tz8utiiXuDgMZBC0r9CyWEG7kUmcHNLiytWRy
weDvGnO+dLjdqS5RCqrBzneUT/BUGk6uQ/04nJlaHlXdlbXNQAuaAsgHP14r9F0z
4xa4m3Bvb/h+8oG8a/8oq7Rv0PfnNga+N/beEtHh+dOcQSBctQ3tOPrwjYPfz+XX
uQIDAQAB
-----END PUBLIC KEY-----
EOF
            );

        $symfonyStyle
            ->expects(self::once())
            ->method('text')
            ->with(
<<<EOF
{
    "keys": [
        {
            "kty": "RSA",
            "n": "68PLgzXPLoc2jRKBBnH6x9pJ3aquMSKvnVdAwi7J3cQwr0kxRlH60YqRSrxPKkeM1pYGMhMBdzpuO4PItHPG5y8bPzf9b7gb6V3g_5hem6QjVJyrp-V8QGYkqk8vHwLUyviIRgeTy2Li-1I4DWucnJXmcLc3ctkURgVtmFpTRqXgj91tz8utiiXuDgMZBC0r9CyWEG7kUmcHNLiytWRyweDvGnO-dLjdqS5RCqrBzneUT_BUGk6uQ_04nJlaHlXdlbXNQAuaAsgHP14r9F0z4xa4m3Bvb_h-8oG8a_8oq7Rv0PfnNga-N_beEtHh-dOcQSBctQ3tOPrwjYPfz-XXuQ",
            "e": "AQAB",
            "kid": "OAuth public key",
            "use": "sig"
        }
    ]
}
EOF
            );

        $command = $this->createCommand();

        $commandTester = $this->testCommand($command);
        $commandTester->assertCommandIsSuccessful();
    }

    /**
     * @return array<array<string>>
     */
    public function paths(): array
    {
        return [
            [
                '/config/openidconnect/keys/public.key',
            ],
            [
                'config/openidconnect/keys/public.key',
            ],
        ];
    }

    private function createCommand(): GenerateJwkConfigurationFileCommand
    {
        return new GenerateJwkConfigurationFileCommand(
            filesystem: $this->fileSystem,
            symfonyStyleFactory: $this->symfonyStyleFactory,
        );
    }

    private function testCommand(GenerateJwkConfigurationFileCommand $command): CommandTester
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            input: [],
        );

        return $commandTester;
    }
}
