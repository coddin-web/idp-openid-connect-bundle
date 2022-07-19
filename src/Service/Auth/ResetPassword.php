<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\Auth;

use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Generator\PasswordResetRequestCreate;
use Doctrine\ORM\EntityManagerInterface;

final class ResetPassword
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function createToken(
        User $user,
    ): string {
        $token = $this->generateResetToken();

        $passwordResetRequest = PasswordResetRequestCreate::create(
            $user,
            $token,
            $this->createValidityDate(),
        );

        $this->entityManager->persist($passwordResetRequest);
        $this->entityManager->flush();

        return $token;
    }

    /**
     * @throws \Exception
     */
    private function generateResetToken(): string
    {
        // TODO: Better exception handling? ; Make length configurable?
        return \bin2hex(\random_bytes(40));
    }

    private function createValidityDate(): \DateTimeImmutable
    {
        $date = new \DateTime();
        // TODO: Make validity of reset token configurable?
        $date->add(new \DateInterval('PT1H'));

        return \DateTimeImmutable::createFromMutable($date);
    }
}
