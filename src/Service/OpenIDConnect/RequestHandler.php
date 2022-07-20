<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\OpenIDConnect;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Exception;
use GuzzleHttp\Psr7\Response as GuzzlePsrResponse;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\AuthCodeRepository;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\RefreshTokenRepository;
use Coddin\IdentityProvider\Service\Psr7Message\Psr7Factory;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class RequestHandler
{
    private PsrHttpFactory $psrHttpFactory;

    /**
     * @throws Exception
     */
    public function __construct(
        private readonly AuthorizationServer $authorizationServer,
        private readonly AuthCodeRepository $authCodeRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        private readonly IdentityRepository $identityRepository,
        private readonly UserOAuthClientVerifier $userOAuthClientVerifier,
        private readonly HttpFoundationFactory $httpFoundationFactory,
    ) {
        $this->enableAuthCodeGrant();

        $this->psrHttpFactory = Psr7Factory::create();
    }

    /**
     * @throws OAuthServerException
     */
    public function respondToAuthorizationRequest(
        Request $request,
        Security $security,
    ): Response {
        $this->initAuthorizationCodeFlowByRequest($request);

        if (!$security->getUser() instanceof UserInterface) {
            return new RedirectResponse($this->urlGenerator->generate('coddin_identity_provider.login'));
        }

        try {
            $authorizationRequest = $this->getCurrentAuthorizationRequest();

            $userEntity = $this->identityRepository->getUserEntityByIdentifier(
                $security->getUser()->getUserIdentifier(),
            );

            $clientEntity = $authorizationRequest->getClient();

            $this->userOAuthClientVerifier->verify($clientEntity, $userEntity);

            $authorizationRequest->setUser($userEntity);
            $authorizationRequest->setAuthorizationApproved(true);

            $authorizationResponse = $this->authorizationServer->completeAuthorizationRequest(
                $authorizationRequest,
                new GuzzlePsrResponse(),
            );

            return $this->httpFoundationFactory->createResponse($authorizationResponse);
        } catch (OAuthServerException $exception) {
            return $this->httpFoundationFactory->createResponse(
                $exception->generateHttpResponse(new GuzzlePsrResponse()),
            );
        } catch (Exception $exception) {
            return new Response(
                $exception->getMessage(),
                500,
            );
        }
    }

    /**
     * @codeCoverageIgnore This would only "test" mocked throwing of Exceptions
     */
    public function respondToTokenRequest(Request $request): Response
    {
        try {
            $accessTokenResponse = $this->authorizationServer->respondToAccessTokenRequest(
                $this->psrHttpFactory->createRequest($request),
                new GuzzlePsrResponse(),
            );

            return $this->httpFoundationFactory->createResponse($accessTokenResponse);
        } catch (OAuthServerException $exception) {
            return $this->httpFoundationFactory->createResponse(
                $exception->generateHttpResponse(new GuzzlePsrResponse()),
            );
        } catch (Exception $exception) {
            return new Response(
                $exception->getMessage(),
                500,
            );
        }
    }

    public function hasCurrentAuthorizationRequest(): bool
    {
        return $this->requestStack->getSession()->has('authorizationRequest');
    }

    /**
     * @throws Exception
     */
    private function enableAuthCodeGrant(): void
    {
        $this->authorizationServer->enableGrantType(
            new AuthCodeGrant(
                $this->authCodeRepository,
                $this->refreshTokenRepository,
                new \DateInterval('PT10M'),
            ),
            new \DateInterval('PT1H'),
        );
    }

    /**
     * @throws OAuthServerException
     */
    private function initAuthorizationCodeFlowByRequest(Request $request): void
    {
        $this->initAuthorizationCodeFlow(function () use ($request) {
            return $this->authorizationServer->validateAuthorizationRequest(
                $this->psrHttpFactory->createRequest($request),
            );
        });
    }

    private function initAuthorizationCodeFlow(callable $authorizationRequestBuilder): void
    {
        $this->resetOpenIDConnectRequestFlow();

        $authorizationRequest = $authorizationRequestBuilder();
        if ($authorizationRequest === null) {
            throw new \LogicException('Builder did not return an AuthorizationRequest');
        }

        $this->setCurrentAuthorizationRequest($authorizationRequest);
    }

    private function getCurrentAuthorizationRequest(): AuthorizationRequest
    {
        if (!$this->hasCurrentAuthorizationRequest()) {
            throw new \LogicException(
                'Missing authorization request, something went wrong with the openIDConnect flow',
            );
        }

        $authRequestFromSession = $this->requestStack->getSession()->get('authorizationRequest');

        if (!is_string($authRequestFromSession)) {
            throw new \LogicException('AuthorizationRequest on Session is invalid');
        }

        $authorizationRequest = unserialize($authRequestFromSession);

        if (!$authorizationRequest instanceof AuthorizationRequest) {
            throw new \LogicException('AuthorizationRequest on Session is invalid');
        }

        return $authorizationRequest;
    }

    private function setCurrentAuthorizationRequest(AuthorizationRequest $authorizationRequest): void
    {
        $this->requestStack->getSession()->set(
            name: 'authorizationRequest',
            value: serialize($authorizationRequest),
        );
    }

    private function resetOpenIDConnectRequestFlow(): void
    {
        $this->requestStack->getSession()->remove(
            name: 'authorizationRequest',
        );
    }

    /**
     * @throws Exception
     */
    public function completeAuthorizationRequest(
        Security $security,
    ): Response {
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw new \Exception('The user could not be retrieved from the Symfony Security flow');
        }

        $userEntity = $this->identityRepository->getUserEntityByIdentifier($user->getUuid());

        $authorizationRequest = $this->getCurrentAuthorizationRequest();
        $authorizationRequest->setUser($userEntity);
        $authorizationRequest->setAuthorizationApproved(true);

        $verifiedAuthorizationRequest = $this->authorizationServer->completeAuthorizationRequest(
            $authorizationRequest,
            new \GuzzleHttp\Psr7\Response(),
        );

        return $this->httpFoundationFactory->createResponse($verifiedAuthorizationRequest);
    }
}
