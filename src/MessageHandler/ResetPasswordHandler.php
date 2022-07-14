<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\MessageHandler;

use Coddin\IdentityProvider\Repository\UserRepository;
use Coddin\IdentityProvider\Message\ResetPassword;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class ResetPasswordHandler
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(ResetPassword $resetPassword): void
    {
        // Check if an account exists with this email,
        // use username in Address object.
        $username = $resetPassword->getEmail();
        $user = $this->userRepository->findOneByUsername($username);

        if ($user === null) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(new Address($username, $user->getUsername()))
            ->subject($this->translator->trans(id: 'email.reset_password.subject', locale: $resetPassword->getLocale()))
            ->htmlTemplate('@CoddinIdentityProvider/email/reset_password.html.twig')
            ->context([
                'labels' => [
                    'salutation' => $this->translator->trans(
                        id: 'email.reset_password.salutation',
                        parameters: ['username' => $username],
                        locale: $resetPassword->getLocale(),
                    ),
                ],
            ]);

        $this->mailer->send($email);
    }
}
