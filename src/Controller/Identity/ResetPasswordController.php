<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller\Identity;

use Coddin\IdentityProvider\Exception\PasswordResetEntityNotFoundException;
use Coddin\IdentityProvider\Repository\PasswordResetRequestRepository;
use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Request\ResetPasswordRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly PasswordResetRequestRepository $passwordResetRequestRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function index(string $token): Response
    {
        return $this->render(
            view: '@CoddinIdentityProvider/password/reset.index.html.twig',
            parameters: [
                'resetToken' => $token,
                'companyName' => $this->getParameter('idp.company_name'),
            ],
        );
    }

    public function process(
        ResetPasswordRequest $resetPasswordRequest,
    ): Response {
        if ($this->isCsrfTokenValid('password_reset', $resetPasswordRequest->getCsrfToken()) === false) {
            // TODO: addFlash.
            return $this->redirectToRoute('coddin_identity_provider.password.reset', ['token' => $resetPasswordRequest->getResetToken()]);
        }

        try {
            $user = $this->passwordResetRequestRepository->getUserForResetToken($resetPasswordRequest->getResetToken());
        } catch (PasswordResetEntityNotFoundException) {
            // TODO: addFlash.
            return $this->redirectToRoute('coddin_identity_provider.password.reset', ['token' => $resetPasswordRequest->getResetToken()]);
        }

        try {
            $this->passwordResetRequestRepository->getValidPasswordResetRequest($user, $resetPasswordRequest->getResetToken());
            $this->userRepository
                ->updatePassword(
                    $user,
                    $resetPasswordRequest->getPassword(),
                );
            $this->passwordResetRequestRepository->invalidateTokenForUser($user, $resetPasswordRequest->getResetToken());
            // TODO: e-mail user telling him his pass has changed.
        } catch (PasswordResetEntityNotFoundException) {
            // TODO: addFlash.
            return $this->redirectToRoute('coddin_identity_provider.password.reset', ['token' => $resetPasswordRequest->getResetToken()]);
        }

        return $this->redirectToRoute('coddin_identity_provider.login');
    }
}
