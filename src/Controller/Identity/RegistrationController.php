<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller\Identity;

use Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository;
use Coddin\IdentityProvider\Message\UserRegistered;
use Coddin\IdentityProvider\Request\UserRegistration;
use Safe\Exceptions\JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserDbalRepository $userDbalRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function index(Request $request): Response
    {
        $errors = [];
        if ($request->getSession()->has('registration_errors')) {
            $errors = $request->getSession()->get('registration_errors');
        }

        $request->getSession()->remove('registration_errors');

        return $this->render(
            '@CoddinIdentityProvider/register/index.html.twig',
            [
                'companyName' => $this->getParameter('idp.company_name'),
                'errors' => $errors,
            ],
        );
    }

    /**
     * @throws JsonException
     */
    public function register(
        UserRegistration $userRegistration,
    ): Response {
        $username = $userRegistration->getUsername();
        $password = $userRegistration->getPassword();

        $existingUser = $this->userDbalRepository->findOneByUsername($username);
        if ($existingUser !== null) {
            $this->addFlash(
                type: 'error',
                message: $this->translator->trans(
                    id: 'account.register.error.existing_user',
                    parameters: [
                        '%username%' => $username,
                    ],
                ),
            );

            return $this->redirectToRoute('coddin_identity_provider.register');
        }

        $user = $this->userDbalRepository->create(
            username: $username,
            password: $password,
            email: $username,
        );

        $this->messageBus->dispatch(new UserRegistered($user->getId()));

        // TODO Thank you / help / start at new application page?
        return $this->redirectToRoute('coddin_identity_provider.login');
    }
}
