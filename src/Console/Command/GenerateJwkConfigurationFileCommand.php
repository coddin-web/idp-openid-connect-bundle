<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Console\Command;

use Coddin\IdentityProvider\Service\Symfony\SymfonyStyleFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Safe\Exceptions\JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

#[AsCommand(
    name: 'coddin:jwk:generate',
    description: 'Generate the JWK configuration file needed for the .well-known/jwks endpoint based on the used public key',
)]
final class GenerateJwkConfigurationFileCommand extends Command
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly SymfonyStyleFactory $symfonyStyleFactory,
    ) {
        parent::__construct();
    }

    /**
     * @throws JsonException
     * phpcs:disable Squiz.Commenting.InlineComment.SpacingAfter
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->symfonyStyleFactory->create($input, $output);
        $io->title('By using the public.key a jwks.json output can be generated');

        try {
            $publicKey = $this->askForPath($io);
        } catch (FilesystemException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $openSSLAsymmetricKey = \openssl_pkey_get_public($publicKey);
        if (!$openSSLAsymmetricKey instanceof \OpenSSLAsymmetricKey) {
            // @codeCoverageIgnoreStart
            $io->error('An unexpected error: `openssl_pkey_get_public` did not return a valid string');

            return Command::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        $keyInfo = \openssl_pkey_get_details($openSSLAsymmetricKey);
        if (is_array($keyInfo) === false) {
            // @codeCoverageIgnoreStart
            $io->error('An unexpected error: `openssl_pkey_get_details` did not return an array');

            return Command::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        $jsonData = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'n' => \rtrim(\str_replace(['+', '/'], ['-', '_'], \base64_encode($keyInfo['rsa']['n'])), '='),
                    'e' => \rtrim(\str_replace(['+', '/'], ['-', '_'], \base64_encode($keyInfo['rsa']['e'])), '='),
                    'kid' => 'OAuth public key',
                    'use' => 'sig',
                ],
            ],
        ];

        $io->info('Save this output to a file named (e.g.) jwks.json and define the path to this file in your environment; `IDP_JWKS_PATH`');
        $io->text(\Safe\json_encode($jsonData, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }

    /**
     * @throws FilesystemException
     */
    private function askForPath(StyleInterface $io): string
    {
        $path = $io->ask(
            question: 'Please give in the path of the public key (this will be relative to the project path)',
        );

        if (is_string($path) === false) {
            throw new InvalidArgumentException('The path should be a string');
        }

        if (\str_starts_with($path, DIRECTORY_SEPARATOR) === false) {
            $path = DIRECTORY_SEPARATOR . $path;
        }

        if ($this->filesystem->fileExists($path) === false) {
            throw new FileNotFoundException('The public key could not be found at the given path');
        }

        return $this->filesystem->read($path);
    }
}
