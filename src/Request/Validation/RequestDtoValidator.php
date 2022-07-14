<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Request\Exception\RequestInvalidException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestDtoValidator
{
    /**
     * @throws RequestInvalidException
     */
    public static function validate(
        ValidatorInterface $validator,
        RequestObjectDtoInterface $dto,
    ): void {
        $errors = $validator->validate($dto);
        if (count($errors) !== 0) {
            // @phpstan-ignore-next-line
            $errors = array_values((array) $errors)[0];
            throw RequestInvalidException::createFromConstraintViolations(...$errors);
        }
    }
}
