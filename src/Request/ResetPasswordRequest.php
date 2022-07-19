<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request;

use Coddin\IdentityProvider\Attribute\RequestValidation;
use Coddin\IdentityProvider\Request\Validation\RequestObjectDtoInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestValidation]
final class ResetPasswordRequest implements RequestObjectDtoInterface
{
    use PasswordRepeatTrait;

    #[Assert\NotBlank(
        message: '`reset_csrf_token` is a required field',
    )]
    private string $resetCsrfToken;
    #[Assert\NotBlank(
        message: '`reset_token` is a required field',
    )]
    private string $resetToken;
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
    public function getCsrfToken(): string
    {
        return $this->resetCsrfToken;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setResetCsrfToken(string $resetCsrfToken): void
    {
        $this->resetCsrfToken = $resetCsrfToken;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getResetToken(): string
    {
        return $this->resetToken;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setResetToken(string $resetToken): void
    {
        $this->resetToken = $resetToken;
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
}
