<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait PasswordRepeatTrait
{
    #[Assert\Callback(
        payload: 'passwords.match.violation',
    )]
    public function validatePasswordRepeat(ExecutionContextInterface $executionContext): void
    {
        /** @var UserRegistration $root */
        $root = $executionContext->getRoot();
        if ($root->getPassword() !== $root->getPasswordRepeat()) {
            $executionContext->addViolation(
                message: 'generic.validation_error.passwords_match',
            );
        }
    }
}
