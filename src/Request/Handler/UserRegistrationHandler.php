<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Handler;

use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

final class UserRegistrationHandler implements RequestConstraintExceptionHandler
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
    ) {
    }

    public function resolve(
        ExceptionEvent $event,
        RequestConstraintException $requestConstraintException,
    ): void {
        /** @var Session $session */
        $session = $this->requestStack->getSession();

        foreach ($requestConstraintException->getConstraintViolationList() as $constraintViolation) {
            $session->getFlashBag()->add(
                type: UserRegistration::FLASH_BAG_ERROR_TYPE,
                message: (string) $constraintViolation->getMessage(),
            );
        }

        $event->setResponse(
            new RedirectResponse(
                url: $this->router->generate('coddin_identity_provider.register'),
            ),
        );
    }
}
