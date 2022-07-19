<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Handler;

use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Coddin\IdentityProvider\Service\Session\ConstraintFlashBagHandler;
use Coddin\IdentityProvider\Service\Symfony\RedirectResponseFactory;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

final class ResetPasswordRequestHandler implements RequestConstraintExceptionHandler
{
    public function __construct(
        private readonly ConstraintFlashBagHandler $constraintFlashBagHandler,
        private readonly RedirectResponseFactory $redirectResponseFactory,
        private readonly RouterInterface $router,
    ) {
    }

    public function resolve(
        ExceptionEvent $event,
        RequestConstraintException $requestConstraintException,
    ): void {
        $this->constraintFlashBagHandler->addMessagesFromException($requestConstraintException);

        $event->setResponse(
            $this->redirectResponseFactory->create(
                url: $this->router->generate(
                    name: 'coddin_identity_provider.password.reset',
                    parameters: [
                        'token' => $event->getRequest()->get('token'),
                    ],
                ),
            ),
        );
    }
}
