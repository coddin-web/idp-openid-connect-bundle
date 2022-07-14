<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class RequestInvalidException extends \Exception implements RequestExceptionInterface
{
    private static int $statusCode = 0;

    public static function createForDenormalization(string $message): self
    {
        self::$statusCode = Response::HTTP_BAD_REQUEST;

        return new self($message);
    }

    public static function createFromConstraintViolations(ConstraintViolationInterface ...$constraintViolations): self
    {
        self::$statusCode = Response::HTTP_CONFLICT;

        $violationMessages = [];
        foreach ($constraintViolations as $constraintViolation) {
            $violationMessages[] = (string) $constraintViolation->getMessage();
        }

        return new self('Constraint violations found: ' . implode('; ', $violationMessages));
    }

    /**
     * @codeCoverageIgnore
     */
    public function getStatusCode(): int
    {
        return self::$statusCode;
    }
}
