<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller\Identity;

use Coddin\IdentityProvider\Message\ResetPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class ForgottenPasswordController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function index(): Response
    {
        return $this->render(
            view: '@CoddinIdentityProvider/password/forgotten.html.twig',
            parameters: [
                'companyName' => $this->getParameter('idp.company_name'),
            ],
        );
    }

    /**
     * @throws \Exception
     */
    public function resetPassword(Request $request): Response
    {
        $resetRequestEmail = $request->request->get('email');

        if (!is_string($resetRequestEmail)) {
            throw new \Exception('The submitted `email` must be a string');
        }

        $this->messageBus->dispatch(
            ResetPassword::create(
                email: $resetRequestEmail,
                locale: $request->getLocale(),
            ),
        );

        return $this->redirectToRoute('coddin_identity_provider.forgotten_password_splash');
    }

    public function splash(): Response
    {
        return $this->render(
            view: '@CoddinIdentityProvider/password/splash.html.twig',
            parameters: [
                'companyName' => $this->getParameter('idp.company_name'),
            ],
        );
    }
}
