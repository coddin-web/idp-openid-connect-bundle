<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request;

use Coddin\IdentityProvider\Request\Validation\RequestObjectDtoInterface;
use Coddin\IdentityProvider\Attribute\RequestValidation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[RequestValidation]
final class UserRegistration implements RequestObjectDtoInterface
{
    public const FLASH_BAG_ERROR_TYPE = 'user_registration.error';

    #[Assert\NotBlank(
        message: '`username` is a required field',
    )]
    private string $username = '';
    #[Assert\NotBlank(
        message: '`password` is a required field',
    )]
    private string $password = '';
    #[Assert\NotBlank(
        message: '`password_repeat` is a required field',
    )]
    private string $passwordRepeat = '';

    /**
     * @codeCoverageIgnore
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPasswordRepeat(string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

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
