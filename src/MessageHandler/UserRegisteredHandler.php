<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\MessageHandler;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient;
use Coddin\IdentityProvider\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Coddin\IdentityProvider\Message\UserRegistered;
use Coddin\IdentityProvider\Service\Guzzle\ClientFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserRegisteredHandler
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly OAuthClientRepository $oauthClientRepository,
        private readonly ClientFactory $clientFactory,
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function __invoke(UserRegistered $userRegistered): void
    {
        $oauthClients = $this->oauthClientRepository->getAll();
        if ($oauthClients->count() === 0) {
            return;
        }

        try {
            $userId = $userRegistered->getUserId();
            $user = $this->userRepository->getOneById($userId);
        } catch (OAuthEntityNotFoundException $e) {
            $this->logger->error(
                sprintf(
                    'After user registration user with id `%d` could not be found to link to the oauthClients',
                    $userId,
                ),
            );
            return;
        }

        $client = $this->clientFactory->create();

        $this->userRepository->assignToOAuthClients($user, ...$oauthClients->all());

        $bearerToken = $this->parameterBag->get('oidc.client.token');
        if (!\is_string($bearerToken)) {
            $this->logger->error(
                // phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found
                'The authorization bearer token could not be retrieved from configuration or is malformed, ' .
                'trying to inform the clients will be futile.',
            );
            return;
        }

        foreach ($oauthClients->all() as $oauthClient) {
            if ($oauthClient->getCreationWebhookUrl() === null) {
                continue;
            }

            try {
                // TODO Do an OPTION call first to determine if the receiving party has setup CORS correctly?
                $client->request(
                    'POST',
                    $oauthClient->getCreationWebhookUrl(),
                    [
                        RequestOptions::HEADERS => [
                            'Authorization' => 'Bearer ' . $bearerToken,
                        ],
                        RequestOptions::JSON => [
                            'uuid' => $user->getUuid(),
                            'username' => $user->getUsername(),
                            'email' => $user->getUsername(),
                        ],
                    ],
                );
            } catch (GuzzleException $e) {
                $this->logError($oauthClient, $e);
            }
        }
    }

    private function logError(
        OAuthClient $oauthClient,
        ?GuzzleException $exception = null,
    ): void {
        $this->logger->error(
            sprintf(
                'While trying to tell the application `%s` a user was created something went wrong',
                $oauthClient->getDisplayName(),
            ),
        );

        if ($exception !== null) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        }
    }
}
