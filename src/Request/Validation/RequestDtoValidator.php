<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestDtoValidator
{
    /**
     * @throws RequestConstraintException
     */
    public static function validate(
        ValidatorInterface $validator,
        RequestObjectDtoInterface $dto,
    ): void {
        $errors = $validator->validate($dto);
        if (count($errors) !== 0) {
            throw RequestConstraintException::create(
                constraintSubject: $dto::class,
                constraintViolationList: $errors,
            );
        }
    }
}
