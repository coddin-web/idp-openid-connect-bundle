<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\Session;

use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class ConstraintFlashBagHandler
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function addMessagesFromException(RequestConstraintException $requestConstraintException): void
    {
        /** @var Session $session */
        $session = $this->requestStack->getSession();

        /** @var ConstraintViolationInterface $constraintViolation */
        foreach ($requestConstraintException->getConstraintViolationList() as $constraintViolation) {
            $session->getFlashBag()->add(
                type: UserRegistration::FLASH_BAG_ERROR_TYPE,
                message: (string) $constraintViolation->getMessage(),
            );
        }
    }
}
