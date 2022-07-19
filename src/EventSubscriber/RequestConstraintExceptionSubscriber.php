<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\EventSubscriber;

use Coddin\IdentityProvider\Request\Handler\ResetPasswordRequestHandler;
use Coddin\IdentityProvider\Request\Handler\UserRegistrationHandler;
use Coddin\IdentityProvider\Request\ResetPasswordRequest;
use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestConstraintExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserRegistrationHandler $userRegistrationHandler,
        private readonly ResetPasswordRequestHandler $resetPasswordRequestHandler,
    ) {
    }

    /**
     * @codeCoverageIgnore
     * phpcs:disable Squiz.Arrays.ArrayDeclaration.SingleLineNotAllowed
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                [ 'process',  10 ],
            ],
        ];
    }

    public function process(ExceptionEvent $event): void
    {
        $requestConstraintException = $event->getThrowable();
        if (!$requestConstraintException instanceof RequestConstraintException) {
            return;
        }

        switch ($requestConstraintException->getConstraintSubject()) {
            case UserRegistration::class:
                $this->userRegistrationHandler->resolve($event, $requestConstraintException);
                break;
            case ResetPasswordRequest::class:
                $this->resetPasswordRequestHandler->resolve($event, $requestConstraintException);
                break;
            default:
                throw new \LogicException('Unsupported RequestConstraintException type encountered');
        }
    }
}
