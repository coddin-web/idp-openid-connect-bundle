<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpMissingFieldTypeInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Service\OpenIDConnect;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\ClientEntity;
use Coddin\IdentityProvider\Entity\LeagueOAuth2Server\UserEntity;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\AuthCodeRepository;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\IdentityRepository;
use Coddin\IdentityProvider\Repository\LeagueOAuth2Server\RefreshTokenRepository;
use Coddin\IdentityProvider\Service\OpenIDConnect\RequestHandler;
use Coddin\IdentityProvider\Service\OpenIDConnect\UserOAuthClientVerifier;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Service\OpenIDConnect\RequestHandler
 * @covers ::__construct
 * @covers ::enableAuthCodeGrant
 */
final class RequestHandlerTest extends TestCase
{
    /** @var AuthorizationServer & MockObject $authorizationServer */
    private $authorizationServer;
    /** @var AuthCodeRepository & MockObject $authCodeRepository */
    private $authCodeRepository;
    /** @var RefreshTokenRepository & MockObject */
    private $refreshTokenRepository;
    /** @var UrlGeneratorInterface & MockObject $urlGenerator */
    private $urlGenerator;
    /** @var RequestStack & MockObject $requestStack */
    private $requestStack;
    /** @var IdentityRepository & MockObject $identityRepository */
    private $identityRepository;
    /** @var UserOAuthClientVerifier & MockObject $userOAuthClientVerifier */
    private $userOAuthClientVerifier;
    /** @var HttpFoundationFactory & MockObject $httpFoundationFactory */
    private $httpFoundationFactory;

    private Request $request;
    /** @var Security & MockObject $security */
    private $security;
    /** @var ResponseInterface & MockObject $authorizationResponse */
    private $authorizationResponse;
    /** @var AuthorizationRequest & MockObject $authorizationRequest */
    private $authorizationRequest;


    protected function setUp(): void
    {
        $this->authorizationServer = $this->createMock(AuthorizationServer::class);
        $this->authCodeRepository = $this->createMock(AuthCodeRepository::class);
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepository::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->identityRepository = $this->createMock(IdentityRepository::class);
        $this->userOAuthClientVerifier = $this->createMock(UserOAuthClientVerifier::class);
        $this->httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);

        $this->security = $this->createMock(Security::class);
        $this->authorizationResponse = $this->createMock(ResponseInterface::class);
        $this->authorizationRequest = $this->createMock(AuthorizationRequest::class);

        $this->authorizationServer
            ->expects(self::once())
            ->method('enableGrantType');
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_user_not_authenticated(): void
    {
        $this->authorizationServer
            ->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->willReturn($this->authorizationRequest);

        $requestHandler = $this->createRequestHandler();

        $url = 'https://idp-frontend.test';
        $this->request = Request::create($url);
        $this->security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        $this->urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->willReturn($url . '/login');

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('remove')
            ->with('authorizationRequest');
        $session
            ->expects(self::once())
            ->method('set')
            ->with('authorizationRequest', serialize($this->authorizationRequest));
        $this->requestStack
            ->expects(self::exactly(2))
            ->method('getSession')
            ->willReturn($session);

        $response = $requestHandler->respondToAuthorizationRequest($this->request, $this->security);
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals('https://idp-frontend.test/login', $response->getTargetUrl());
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @covers ::initAuthorizationCodeFlowByRequest
     * @covers ::initAuthorizationCodeFlow
     * @covers ::resetOpenIDConnectRequestFlow
     * @covers ::setCurrentAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_user_authenticated(): void
    {
        $this->setupRespondToAuthRequest();

        $this->authorizationServer
            ->expects(self::once())
            ->method('completeAuthorizationRequest')
            ->with($this->authorizationRequest)
            ->willReturn($this->authorizationResponse);

        $this->httpFoundationFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with($this->authorizationResponse)
            ->willReturn($this->createMock(Response::class));

        $requestHandler = $this->createRequestHandler();
        $requestHandler->respondToAuthorizationRequest($this->request, $this->security);
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @covers ::initAuthorizationCodeFlowByRequest
     * @covers ::initAuthorizationCodeFlow
     * @covers ::resetOpenIDConnectRequestFlow
     * @covers ::setCurrentAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_OauthServerException(): void
    {
        $this->setupRespondToAuthRequest();

        $expectedException = $this->createMock(OAuthServerException::class);
        $expectedException
            ->expects(self::once())
            ->method('generateHttpResponse')
            ->willReturn(new \GuzzleHttp\Psr7\Response());

        $this->authorizationServer
            ->expects(self::once())
            ->method('completeAuthorizationRequest')
            ->with($this->authorizationRequest)
            ->willThrowException($expectedException);

        $this->httpFoundationFactory
            ->expects(self::once())
            ->method('createResponse')
            ->willReturn($this->createMock(Response::class));

        $requestHandler = $this->createRequestHandler();
        $requestHandler->respondToAuthorizationRequest($this->request, $this->security);
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @covers ::initAuthorizationCodeFlowByRequest
     * @covers ::initAuthorizationCodeFlow
     * @covers ::resetOpenIDConnectRequestFlow
     * @covers ::setCurrentAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_generic_Exception(): void
    {
        $this->setupRespondToAuthRequest();

        $expectedException = new \Exception('Generic error message');

        $this->authorizationServer
            ->expects(self::once())
            ->method('completeAuthorizationRequest')
            ->with($this->authorizationRequest)
            ->willThrowException($expectedException);

        $this->httpFoundationFactory
            ->expects(self::never())
            ->method('createResponse');

        $requestHandler = $this->createRequestHandler();
        $response = $requestHandler->respondToAuthorizationRequest($this->request, $this->security);

        self::assertEquals(500, $response->getStatusCode());
        self::assertEquals('Generic error message', $response->getContent());
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @covers ::initAuthorizationCodeFlowByRequest
     * @covers ::initAuthorizationCodeFlow
     * @covers ::resetOpenIDConnectRequestFlow
     * @covers ::setCurrentAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_incorrect_builder(): void
    {
        $url = 'https://idp-frontend.test';
        $this->request = Request::create($url);

        $this->authorizationServer
            ->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->willReturn(null);

        $this->httpFoundationFactory
            ->expects(self::never())
            ->method('createResponse');

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Builder did not return an AuthorizationRequest');

        $requestHandler = $this->createRequestHandler();
        $requestHandler->respondToAuthorizationRequest($this->request, $this->security);
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @covers ::initAuthorizationCodeFlowByRequest
     * @covers ::initAuthorizationCodeFlow
     * @covers ::resetOpenIDConnectRequestFlow
     * @covers ::setCurrentAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_missing_authorizationRequest_on_session(): void
    {
        $url = 'https://idp-frontend.test';
        $this->request = Request::create($url);

        $this->authorizationServer
            ->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->willReturn($this->authorizationRequest);

        $user = $this->createMock(User::class);
        $this->security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $session = $this->createMock(Session::class);
        $session->expects(self::once())
            ->method('has')
            ->with('authorizationRequest')
            ->willReturn(false);
        $this->requestStack
            ->expects(self::exactly(3))
            ->method('getSession')
            ->willReturn($session);

        $requestHandler = $this->createRequestHandler();
        $response = $requestHandler->respondToAuthorizationRequest($this->request, $this->security);

        self::assertEquals(
            'Missing authorization request, something went wrong with the openIDConnect flow',
            $response->getContent(),
        );
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @covers ::initAuthorizationCodeFlowByRequest
     * @covers ::initAuthorizationCodeFlow
     * @covers ::resetOpenIDConnectRequestFlow
     * @covers ::setCurrentAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_incorrect_authorizationRequest_on_session(): void
    {
        $url = 'https://idp-frontend.test';
        $this->request = Request::create($url);

        $this->authorizationServer
            ->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->willReturn($this->authorizationRequest);

        $user = $this->createMock(User::class);
        $this->security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('has')
            ->with('authorizationRequest')
            ->willReturn(true);
        $session
            ->expects(self::once())
            ->method('get')
            ->with('authorizationRequest')
            ->willReturn(new \stdClass());
        $this->requestStack
            ->expects(self::exactly(4))
            ->method('getSession')
            ->willReturn($session);

        $requestHandler = $this->createRequestHandler();
        $response = $requestHandler->respondToAuthorizationRequest($this->request, $this->security);

        self::assertEquals(
            'AuthorizationRequest on Session is invalid',
            $response->getContent(),
        );
    }

    /**
     * @test
     * @covers ::respondToAuthorizationRequest
     * @covers ::initAuthorizationCodeFlowByRequest
     * @covers ::initAuthorizationCodeFlow
     * @covers ::resetOpenIDConnectRequestFlow
     * @covers ::setCurrentAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function respond_to_authorizationRequest_incorrect_instance_authorizationRequest_on_session(): void
    {
        $url = 'https://idp-frontend.test';
        $this->request = Request::create($url);

        $this->authorizationServer
            ->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->willReturn($this->authorizationRequest);

        $user = $this->createMock(User::class);
        $this->security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('has')
            ->with('authorizationRequest')
            ->willReturn(true);
        $session
            ->expects(self::once())
            ->method('get')
            ->with('authorizationRequest')
            ->willReturn(serialize(new \stdClass()));
        $this->requestStack
            ->expects(self::exactly(4))
            ->method('getSession')
            ->willReturn($session);

        $requestHandler = $this->createRequestHandler();
        $response = $requestHandler->respondToAuthorizationRequest($this->request, $this->security);

        self::assertEquals(
            'AuthorizationRequest on Session is invalid',
            $response->getContent(),
        );
    }

    /**
     * @test
     * @covers ::hasCurrentAuthorizationRequest
     */
    public function has_current_AuthorizationRequest(): void
    {
        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('has')
            ->with('authorizationRequest')
            ->willReturn(true);

        $this->requestStack
            ->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        $requestHandler = $this->createRequestHandler();
        self::assertTrue($requestHandler->hasCurrentAuthorizationRequest());
    }

    /**
     * @test
     * @covers ::completeAuthorizationRequest
     * @covers ::getCurrentAuthorizationRequest
     */
    public function complete_authorization_request_user_not_authenticated(): void
    {
        $this->security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('The user could not be retrieved from the Symfony Security flow');

        $requestHandler = $this->createRequestHandler();
        $requestHandler->completeAuthorizationRequest($this->security);
    }

    /**
     * @test
     * @covers ::completeAuthorizationRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function complete_authorization_request(): void
    {
        $user = $this->createMock(User::class);
        $user
            ->expects(self::once())
            ->method('getUuid')
            ->willReturn('fancy_uuid');

        $this->security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $userEntity = $this->createMock(UserEntity::class);

        $this->identityRepository
            ->expects(self::once())
            ->method('getUserEntityByIdentifier')
            ->with('fancy_uuid')
            ->willReturn($userEntity);

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('has')
            ->with('authorizationRequest')
            ->willReturn(true);

        $session
            ->expects(self::once())
            ->method('get')
            ->willReturn(serialize($this->authorizationRequest));
        $this->requestStack
            ->expects(self::exactly(2))
            ->method('getSession')
            ->willReturn($session);

        $this->authorizationRequest
            ->method('setUser')
            ->with($userEntity);

        $this->authorizationRequest
            ->method('setAuthorizationApproved')
            ->with(true);

        $verifiedAuthorizationRequest = $this->createMock(ResponseInterface::class);
        $this->authorizationServer
            ->expects(self::once())
            ->method('completeAuthorizationRequest')
            ->with($this->authorizationRequest)
            ->willReturn($verifiedAuthorizationRequest);

        $this->httpFoundationFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with($verifiedAuthorizationRequest)
            ->willReturn($this->createMock(Response::class));

        $requestHandler = $this->createRequestHandler();
        $requestHandler->completeAuthorizationRequest($this->security);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function createRequestHandler(): RequestHandler
    {
        return new RequestHandler(
            $this->authorizationServer,
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            $this->urlGenerator,
            $this->requestStack,
            $this->identityRepository,
            $this->userOAuthClientVerifier,
            $this->httpFoundationFactory,
        );
    }

    private function setupRespondToAuthRequest(): void
    {
        $this->authorizationServer
            ->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->willReturn($this->authorizationRequest);

        $user = $this->createMock(User::class);
        $user
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('fancy_uuid');

        $userEntity = $this->createMock(UserEntity::class);

        $this->identityRepository
            ->expects(self::once())
            ->method('getUserEntityByIdentifier')
            ->with('fancy_uuid')
            ->willReturn($userEntity);

        $clientEntity = $this->createMock(ClientEntity::class);

        $this->userOAuthClientVerifier
            ->expects(self::once())
            ->method('verify')
            ->with($clientEntity, $userEntity);

        $url = 'https://idp-frontend.test';
        $this->request = Request::create($url);
        $this->request->attributes->set('user_authenticated', true);

        $this->security
            ->expects(self::exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('remove')
            ->with('authorizationRequest');

        // It is not possible to check the serialized data because the mocked class differentiates
        // after it is invoked.
        $session
            ->expects(self::once())
            ->method('set')
            ->with('authorizationRequest');
        $session
            ->expects(self::once())
            ->method('has')
            ->with('authorizationRequest')
            ->willReturn(true);

        // Not working because of the (un)serialization of the return object, because afterwards it's
        // not the same as the original mocked object.
        $this->authorizationRequest
            // ->expects(self::once())
            ->method('getClient')
            ->willReturn($clientEntity);

        $session
            ->expects(self::once())
            ->method('get')
            ->willReturn(serialize($this->authorizationRequest));
        $this->requestStack
            ->expects(self::exactly(4))
            ->method('getSession')
            ->willReturn($session);
    }
}
