<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller\Identity;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Coddin\IdentityProvider\Repository\Dbal\UserDbalRepository;
use Coddin\IdentityProvider\Message\UserRegistered;
use Safe\Exceptions\JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserDbalRepository $userDbalRepository,
        private readonly MessageBusInterface $messageBus,
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
            'register/index.html.twig',
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
        Request $request,
    ): Response {
        $errors = [];
        $postContent = $request->request->all();

        try {
            Assertion::keyExists($postContent, 'username');
            Assertion::keyExists($postContent, 'password');
            Assertion::keyExists($postContent, 'password_repeat');
        } catch (AssertionFailedException) {
            $errors['general'] = 'Missing required field(s)';

            $request->getSession()->set('registration_errors', $errors);

            return $this->redirectToRoute('register');
        }

        $username = $postContent['username'];
        $password = $postContent['password'];
        $passwordRepeat = $postContent['password_repeat'];
        if (!\is_string($username) || !\is_string($password) || !\is_string($passwordRepeat)) {
            return $this->redirectToRoute('register');
        }

        $existingUser = $this->userDbalRepository->findOneByUsername($username);
        if ($existingUser !== null) {
            $errors['general'] = sprintf(
                'User with username `%s` already exists, maybe you forgot your password?',
                $username,
            );

            $request->getSession()->set('registration_errors', $errors);

            return $this->redirectToRoute('register');
        }

        if ($password !== $passwordRepeat) {
            $errors['general'] = 'Passwords do not match';

            $request->getSession()->set('registration_errors', $errors);

            return $this->redirectToRoute('register');
        }

        // TODO Validate password strength?
        $user = $this->userDbalRepository->create(
            username: $username,
            password: $password,
            email: $username,
        );

        $this->messageBus->dispatch(new UserRegistered($user->getId()));

        // TODO Thank you / help / start at new application page?
        return $this->redirectToRoute('login');
    }
}
