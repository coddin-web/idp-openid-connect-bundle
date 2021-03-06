<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request;

use Coddin\IdentityProvider\Request\Validation\RequestObjectDtoInterface;
use Coddin\IdentityProvider\Attribute\RequestValidation;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestValidation]
final class UserRegistration implements RequestObjectDtoInterface
{
    use PasswordRepeatTrait;

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
}
