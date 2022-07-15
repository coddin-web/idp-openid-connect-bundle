<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Validation\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class RequestConstraintException extends \Exception
{
    /**
     * @param class-string $constraintSubject
     */
    public function __construct(
        private readonly string $constraintSubject,
        private readonly ConstraintViolationListInterface $constraintViolationList,
    ) {
        parent::__construct('A constraint violation has occurred');
    }

    /**
     * @param class-string $constraintSubject
     */
    public static function create(
        string $constraintSubject,
        ConstraintViolationListInterface $constraintViolationList,
    ): self {
        return new self($constraintSubject, $constraintViolationList);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getConstraintSubject(): string
    {
        return $this->constraintSubject;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
