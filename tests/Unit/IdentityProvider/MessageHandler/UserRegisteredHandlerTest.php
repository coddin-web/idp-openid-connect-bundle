<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\IdentityProvider\MessageHandler;

use Coddin\IdentityProvider\Collection\OAuthClientCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Coddin\IdentityProvider\Message\UserRegistered;
use Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler;
use Coddin\IdentityProvider\Service\Guzzle\ClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class UserRegisteredHandlerTest extends TestCase
{
    /** @var UserRepository & MockObject $userRepository */
    private $userRepository;
    /** @var OAuthClientRepository & MockObject $oauthClientRepository */
    private $oauthClientRepository;
    /** @var ClientFactory & MockObject $clientFactory */
    private $clientFactory;
    /** @var LoggerInterface & MockObject $logger */
    private $logger;
    /** @var ParameterBagInterface & MockObject $parameterBag */
    private $parameterBag;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->oauthClientRepository = $this->createMock(OAuthClientRepository::class);
        $this->clientFactory = $this->createMock(ClientFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler::__invoke
     */
    public function no_oauth_clients(): void
    {
        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn(OAuthClientCollection::create([]));

        $this->userRepository
            ->expects(self::never())
            ->method('getOneById');

        $this->userRepository
            ->expects(self::never())
            ->method('assignToOAuthClients');

        $userRegistered = $this->createMock(UserRegistered::class);

        $userRegisteredHandler = $this->createUserRegisteredHandler();
        $userRegisteredHandler($userRegistered);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler::__invoke
     * @covers \Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler::logError
     */
    public function cannot_find_user(): void
    {
        $oauthClient = $this->createMock(OAuthClient::class);
        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn(OAuthClientCollection::create([$oauthClient]));

        $userRegistered = $this->createMock(UserRegistered::class);
        $userRegistered
            ->expects(self::once())
            ->method('getUserId')
            ->willReturn(1);

        $this->userRepository
            ->expects(self::once())
            ->method('getOneById')
            ->with(1)
            ->willThrowException(new OAuthEntityNotFoundException());

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('After user registration user with id `1` could not be found to link to the oauthClients');

        $this->userRepository
            ->expects(self::never())
            ->method('assignToOAuthClients');

        $userRegisteredHandler = $this->createUserRegisteredHandler();
        $userRegisteredHandler($userRegistered);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler::__invoke
     * @covers \Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler::logError
     */
    public function missing_bearer_token(): void
    {
        $oauthClient = $this->createMock(OAuthClient::class);
        $oauthClients = OAuthClientCollection::create([$oauthClient]);
        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn($oauthClients);

        $userRegistered = $this->createMock(UserRegistered::class);
        $userRegistered
            ->expects(self::once())
            ->method('getUserId')
            ->willReturn(1);

        $user = $this->createMock(User::class);
        $this->userRepository
            ->expects(self::once())
            ->method('getOneById')
            ->with(1)
            ->willReturn($user);

        $client = $this->createMock(Client::class);
        $this->clientFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($client);

        $this->userRepository
            ->expects(self::once())
            ->method('assignToOAuthClients')
            ->with($user, ...$oauthClients->all());

        $this->parameterBag
            ->expects(self::once())
            ->method('get')
            ->with('oidc.client.token')
            ->willReturn(null);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                // phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found
                'The authorization bearer token could not be retrieved from configuration or is malformed, ' .
                'trying to inform the clients will be futile.',
            );

        $userRegisteredHandler = $this->createUserRegisteredHandler();
        $userRegisteredHandler($userRegistered);
    }

    /**
     * @test
     * @covers \Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler::__invoke
     * @covers \Coddin\IdentityProvider\MessageHandler\UserRegisteredHandler::logError
     */
    public function process_oauth_clients(): void
    {
        $oauthClientNoWebhookUrl = $this->createMock(OAuthClient::class);
        $oauthClientNoWebhookUrl
            ->expects(self::once())
            ->method('getCreationWebhookUrl')
            ->willReturn(null);

        $oauthClient = $this->createMock(OAuthClient::class);
        $oauthClient
            ->expects(self::exactly(2))
            ->method('getCreationWebhookUrl')
            ->willReturn('https://foo.bar.test/webhook');

        $oauthClientGuzzleException = $this->createMock(OAuthClient::class);
        $oauthClientGuzzleException
            ->expects(self::exactly(2))
            ->method('getCreationWebhookUrl')
            ->willReturn('https://foo.bar.test/webhook');
        $oauthClientGuzzleException
            ->expects(self::once())
            ->method('getDisplayName')
            ->willReturn('oauthClientWithException');

        $oauthClients = OAuthClientCollection::create([
            $oauthClientNoWebhookUrl,
            $oauthClient,
            $oauthClientGuzzleException,
        ]);
        $this->oauthClientRepository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn($oauthClients);

        $userRegistered = $this->createMock(UserRegistered::class);
        $userRegistered
            ->expects(self::once())
            ->method('getUserId')
            ->willReturn(1);

        $user = $this->createMock(User::class);
        $this->userRepository
            ->expects(self::once())
            ->method('getOneById')
            ->with(1)
            ->willReturn($user);

        $client = $this->createMock(Client::class);
        $client
            ->expects(self::exactly(2))
            ->method('request')
            ->willReturn($this->createMock(ResponseInterface::class));
        $this->clientFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($client);

        $this->userRepository
            ->expects(self::once())
            ->method('assignToOAuthClients')
            ->with($user, ...$oauthClients->all());

        $this->parameterBag
            ->expects(self::once())
            ->method('get')
            ->with('oidc.client.token')
            ->willReturn('this_is_a_bearer_token');

        $matcher = self::exactly(2);
        $client
            ->expects($matcher)
            ->method('request')
            ->willReturnCallback(function () use ($matcher) {
                if ($matcher->getInvocationCount() === 1) {
                    return;
                }

                throw new BadResponseException(
                    message: 'BadResponseException',
                    request: $this->createMock(RequestInterface::class),
                    response: $this->createMock(ResponseInterface::class),
                );
            });

        $this->logger
            ->expects(self::exactly(2))
            ->method('error')
            ->withConsecutive(
                ['While trying to tell the application `oauthClientWithException` a user was created something went wrong'], // phpcs:ignore
                ['BadResponseException'],
            );

        $userRegisteredHandler = $this->createUserRegisteredHandler();
        $userRegisteredHandler($userRegistered);
    }

    private function createUserRegisteredHandler(): UserRegisteredHandler
    {
        return new UserRegisteredHandler(
            userRepository: $this->userRepository,
            oauthClientRepository: $this->oauthClientRepository,
            clientFactory: $this->clientFactory,
            logger: $this->logger,
            parameterBag: $this->parameterBag,
        );
    }
}
