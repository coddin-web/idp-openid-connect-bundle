<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Coddin\IdentityProvider\Collection\MultiFactorAuthentication\AccountMfaMethodCollection;
use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaMethod;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethodConfig;
use Coddin\IdentityProvider\Repository\MfaMethodRepository;
use Coddin\IdentityProvider\Repository\UserMfaMethodRepository;
use Coddin\IdentityProvider\Service\Auth\MfaProvider;
use OTPHP\TOTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

final class AccountController extends AbstractController
{
    public function __construct(
        private readonly MfaMethodRepository $mfaMethodRepository,
        private readonly UserMfaMethodRepository $userMfaMethodRepository,
        private readonly MfaProvider $mfaProvider,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function profile(
        Security $security,
    ): Response {
        return $this->render(
            view: '@CoddinIdentityProvider/account/profile.html.twig',
            parameters: [
                'user' => $security->getUser(),
            ],
        );
    }

    public function security(
        Security $security,
    ): Response {
        /** @var User $user */
        $user = $security->getUser();

        $accountMfaMethods = AccountMfaMethodCollection::create(
            mfaMethods: $this->mfaMethodRepository->getAll(),
            user: $user,
        );

        return $this->render(
            view: '@CoddinIdentityProvider/account/security.html.twig',
            parameters: [
                'mfaMethods' => $accountMfaMethods->all(),
            ],
        );
    }

    public function deleteMfa(
        Request $request,
        Security $security,
    ): Response {
        /** @var User $user */
        $user = $security->getUser();

        // Todo: Validation.
        $userMfaMethodId = $request->request->get('user_mfa_method_id');
        $this->userMfaMethodRepository->deleteForUserById((int) $userMfaMethodId, $user);

        return $this->redirectToRoute('coddin_identity_provider.account.security');
    }

    /**
     * @throws \Exception
     */
    public function setupMfa(
        Security $security,
        string $mfaIdentifier,
    ): Response {
        /** @var User $user */
        $user = $security->getUser();

        // Todo: This is only for OTP based MFA's, determine this based on route for example.
        $oneTimePassword = TOTP::create();
        $secret = $oneTimePassword->getSecret();

        // Todo: Determine (by route for example) which otp method is running the setup.
        $mfaMethod = MfaMethod::METHOD_AUTHENTICATOR_APP;
        $mfaMethodConfigKey = match ($mfaMethod) {
            MfaMethod::METHOD_AUTHENTICATOR_APP => 'totp_secret_key',
            default => throw new \Exception('Not implemented yet'),
        };

        $this->mfaProvider->mfaMethodRegistration(
            $user,
            MfaMethod::METHOD_AUTHENTICATOR_APP,
            [
                $mfaMethodConfigKey => $secret,
            ],
        );

        // Todo: This is Authenticator App specific, move this logic to a Service and determine if this even needs to happen.
        $qrRendered = new ImageRenderer(
            rendererStyle: new RendererStyle(400),
            imageBackEnd: new SvgImageBackEnd(),
        );
        $writer = new Writer($qrRendered);
        $qrCodeData = \sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s',
            $user->getUsername(),
            $secret,
            /* @phpstan-ignore-next-line */
            $this->parameterBag->get('idp.company_name'),
        );
        $qrCodeRaw = $writer->writeString($qrCodeData);

        return $this->render(
            view: '@CoddinIdentityProvider/account/security/setup_mfa.html.twig',
            parameters: [
                'qrCodeData' => $qrCodeRaw,
            ],
        );
    }

    public function validateMfa(
        Request $request,
        Security $security,
        string $mfaIdentifier,
    ): Response {
        // Todo: Add global CSRF validation.
        // Todo: Validate input?
        $otp = $request->request->get('mfa_validate');

        /** @var User $user */
        $user = $security->getUser();

        $userMfaMethodForType = $this->userMfaMethodRepository->getUnConfiguredMfaMethodForUser(
            $user,
            MfaMethod::fromValue($mfaIdentifier),
        );

        // Todo: This should be in the AuthenticatorAppMethodHandler::class.
        // Todo: Move key/value logic to a service/helper that handles getting the correct value.
        $userMfaMethodConfig = $userMfaMethodForType->getUserMfaMethodConfigs();
        /** @var UserMfaMethodConfig $userMfaMethodSecret */
        $userMfaMethodSecret = $userMfaMethodConfig->filter(
            fn(UserMfaMethodConfig $userMfaMethodConfig) => $userMfaMethodConfig->getKey() === 'totp_secret_key',
        )[0];

        /* @phpstan-ignore-next-line Fix validation */
        $verified = TOTP::create($userMfaMethodSecret->getValue())->verify($otp);
        if ($verified === false) {
            // Todo: Handle errors.
            $this->redirectToRoute('coddin_identity_provider.account.profile');
        }

        $this->userMfaMethodRepository->setValidated($userMfaMethodForType);
        $this->userMfaMethodRepository->setActive($userMfaMethodForType);

        return $this->redirectToRoute('coddin_identity_provider.account.security');
    }
}
