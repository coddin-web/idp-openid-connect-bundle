<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller\Identity;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Repository\OAuthAccessTokenRepository;
use Coddin\IdentityProvider\Repository\OAuthClientRepository;
use Coddin\IdentityProvider\Exception\OAuthEntityNotFoundException;
use Coddin\IdentityProvider\Service\Auth\MfaProvider;
use Exception;
use Coddin\IdentityProvider\Helper\OAuthOpenIDConnectDataHelperInterface;
use Coddin\IdentityProvider\Service\OpenIDConnect\ConfigGenerator;
use Coddin\IdentityProvider\Service\OpenIDConnect\RequestHandler;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use League\OAuth2\Server\Exception\OAuthServerException;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\UrlException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthorizationController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly RequestHandler $openIDConnectRequestHandler,
        private readonly OAuthOpenIDConnectDataHelperInterface $oAuthOpenIDConnectDataHelper,
        private readonly OAuthClientRepository $oauthClientRepository,
        private readonly OAuthOpenIDConnectDataHelperInterface $oauthOpenIDConnectDataHelper,
        private readonly OAuthAccessTokenRepository $oauthAccessTokenRepository,
        private readonly MfaProvider $mfaProvider,
    ) {
    }

    public function login(
        Security $security,
        AuthenticationUtils $authenticationUtils,
    ): Response {
        if ($security->getUser() !== null) {
            /** @var User $user */
            $user = $security->getUser();

            // Todo: Create a setting for MFA obligatory or not?
            if ($this->mfaProvider->hasActiveMfa($user) && $this->mfaProvider->isVerified() === false) {
                return $this->redirectToRoute('coddin_identity_provider.mfa');
            }

            return $this->redirectToRoute(
                route: $this->parameterBag->get('coddin_identity_provider.after_authorization_redirect_route_name')
            );
        }

        return $this->render(
            '@CoddinIdentityProvider/login/index.html.twig',
            [
                'lastUsername' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError(),
            ],
        );
    }

    /**
     * @throws Exception
     */
    public function finishOpenIDConnectFlow(
        Security $security,
    ): Response {
        if (!$security->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('coddin_identity_provider.login');
        }

        /** @var User $user */
        $user = $security->getUser();

        // Todo: Create a setting for MFA obligatory or not?
        if ($this->mfaProvider->hasActiveMfa($user) && $this->mfaProvider->isVerified() === false) {
            return $this->redirectToRoute('coddin_identity_provider.mfa');
        }

        // If we got here with an authenticated user but do not have an authorizationRequest, the login
        // must have happened on the IDP.
        if ($this->openIDConnectRequestHandler->hasCurrentAuthorizationRequest() === false) {
            return $this->redirectToRoute(
                route: $this->parameterBag->get('coddin_identity_provider.after_authorization_redirect_route_name')
            );
        }

        return $this->openIDConnectRequestHandler->completeAuthorizationRequest($security);
    }

    /**
     * @throws OAuthServerException
     */
    public function authorize(
        Request $request,
        Security $security,
    ): Response {
        return $this->openIDConnectRequestHandler->respondToAuthorizationRequest($request, $security);
    }

    public function token(Request $request): Response
    {
        return $this->openIDConnectRequestHandler->respondToTokenRequest($request);
    }

    /**
     * @throws UrlException
     * @throws \Exception
     */
    public function introspect(Request $request): Response
    {
        $authorization = $request->headers->get('Authorization');

        if ($authorization === null) {
            throw new \Exception('Missing Authorization header');
        }

        $authorizationParts = \explode(' ', $authorization);
        if ($authorizationParts[0] !== 'Basic') {
            throw new \Exception('Incorrect Authorization type detected');
        }

        $decodedBasicAuthorization = \Safe\base64_decode($authorizationParts[1], true);
        $decodedBasicAuthorizationParts = \explode(':', $decodedBasicAuthorization);

        $oauthClient = $this->oauthClientRepository->getOneByExternalId(
            externalId: $decodedBasicAuthorizationParts[0],
        );

        $clientSecretVerified = password_verify(
            password: $decodedBasicAuthorizationParts[1],
            hash: $oauthClient->getSecret(),
        );

        if (!$clientSecretVerified) {
            throw new \Exception('Authorization failed for the client');
        }

        $tokenString = $request->request->get('token');
        if (!\is_string($tokenString)) {
            throw new \Exception('Stuff went wrong yo');
        }

        $token = $this->parseToken($tokenString);
        // Use the JWT validator.
        if ($token->isExpired(new \DateTimeImmutable())) {
            return new JsonResponse(['active' => false]);
        }

        $accessTokenEntity = $this->oauthAccessTokenRepository->getOneByExternalId(
        /* @phpstan-ignore-next-line */
            externalId: $token->claims()->get('jti'),
        );
        if ($accessTokenEntity->getRevokedAt() !== null) {
            return new JsonResponse(['active' => false]);
        }

        return new JsonResponse(['active' => true]);
    }

    /**
     * @throws OAuthEntityNotFoundException
     */
    public function endSession(
        Request $request,
        TokenStorageInterface $tokenStorage,
    ): Response {
        $tokenHint = $request->query->get('id_token_hint');
        $redirectUrl = ($request->query->get('post_logout_redirect_uri') ?? '/login');

        $accessTokenEntity = $this->oauthAccessTokenRepository->getOneByExternalId(
        /* @phpstan-ignore-next-line */
            externalId: $tokenHint,
        );
        $this->oauthAccessTokenRepository->revoke(accessToken: $accessTokenEntity);

        $token = new NullToken();
        $tokenStorage->setToken($token);

        $session = $request->getSession();
        $session->invalidate();

        return $this->redirect($redirectUrl);
    }

    /**
     * @throws JsonException
     */
    public function wellKnown(Request $request): JsonResponse
    {
        $openIdConfiguration = ConfigGenerator::create($request->getHost());

        return new JsonResponse($openIdConfiguration->asArray());
    }

    /**
     * @throws FilesystemException
     */
    public function wellKnownJsonWebKeys(Request $request): JsonResponse
    {
        return new JsonResponse(
            json_decode($this->oAuthOpenIDConnectDataHelper->getJsonWebKeysFromConfig()),
        );
    }

    /**
     * @throws Exception
     */
    private function parseToken(string $tokenString): Plain
    {
        $signer = new Sha256();
        $key = InMemory::base64Encoded(
            contents: \base64_encode(
                $this->oauthOpenIDConnectDataHelper->privateKeyCryptKey()->getKeyContents(),
            ),
        );

        $configuration = Configuration::forSymmetricSigner(
            signer: $signer,
            key: $key,
        );

        $configuration
            ->setValidationConstraints(
                new SignedWith(
                    signer: $signer,
                    key: $key,
                ),
                new StrictValidAt(
                    clock: new FrozenClock(new \DateTimeImmutable()),
                ),
            );

        $token = $configuration->parser()->parse($tokenString);

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$token instanceof Plain) {
            throw new \Exception('Decoding the token went wrong');
        }

        return $token;
    }
}
