<?php

namespace Coddin\IdentityProvider\Request\Handler;

use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

interface RequestConstraintExceptionHandler
{
    public function resolve(
        ExceptionEvent $event,
        RequestConstraintException $requestConstraintException,
    ): void;
}
