<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller\Auth;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Repository\UserMfaMethodRepository;
use Coddin\IdentityProvider\Service\Auth\MfaProvider;
use Coddin\IdentityProvider\Service\MultiFactorAuthentication\FlowHandler as MfaFlowHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

final class MfaController extends AbstractController
{
    public function __construct(
        private readonly MfaProvider $mfaProvider,
        private readonly MfaFlowHandler $mfaFlowHandler,
        private readonly UserMfaMethodRepository $userMfaMethodRepository,
    ) {
    }

    public function index(
        Request $request,
        Security $security,
    ): Response {
        $user = $security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('coddin_identity_provider.login');
        }

        $lastError = null;
        if ($request->getSession()->getFlashBag()->has('errors_for_mfa')) {
            $lastError = $request->getSession()->getFlashBag()->get('errors_for_mfa');
        }

        $userMfaMethod = $this->userMfaMethodRepository->getActiveMfaMethodForUser($user);

        return $this->render(
            view: '@CoddinIdentityProvider/login/mfa.index.html.twig',
            parameters: [
                'lastError' => $lastError,
                'mfaType' => $userMfaMethod->getMfaMethod()->getType(),
            ],
        );
    }

    // Todo: Add attribute to redirect when no user!
    public function requestOtp(
        Security $security,
    ): Response {
        $user = $security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('coddin_identity_provider.login');
        }

        try {
            $this->mfaFlowHandler->sendOneTimePasswordToUser($user);

            return new Response();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), $e->getCode());
        }
    }

    public function process(
        Request $request,
        Security $security,
    ): Response {
        try {
            $this->mfaFlowHandler->processSubmittedMfa($request, $security);
        } catch (\Exception $e) {
            // Redirect with errors.
            $request->getSession()->getFlashBag()->add('errors_for_mfa', $e->getMessage());

            return $this->redirectToRoute('coddin_identity_provider.mfa');
        }

        // Do a lot of stuff.
        $this->mfaProvider->setMfaVerified();

        return $this->redirectToRoute('coddin_identity_provider.login_finish_oid');
    }
}
