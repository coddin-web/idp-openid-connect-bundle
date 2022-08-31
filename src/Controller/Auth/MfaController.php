<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller\Auth;

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
    ) {
    }

    public function index(
        Request $request,
        Security $security,
    ): Response {
        if ($security->getUser() === null) {
            return $this->redirectToRoute('coddin_identity_provider.login');
        }

        // Todo remove.
        if ($request->getSession()->getFlashBag()->has('errors_for_mfa')) {
            dump($request->getSession()->getFlashBag()->get('errors_for_mfa'));
        }

        return $this->render(
            '@CoddinIdentityProvider/login/mfa.index.html.twig',
        );
    }

    public function process(
        Request $request,
        Security $security,
    ): Response {
        try {
            $this->mfaFlowHandler->handleActiveMfa($request, $security);
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
