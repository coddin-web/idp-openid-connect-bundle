<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Handler;

use Coddin\IdentityProvider\Request\Handler\UserRegistrationHandler;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Coddin\IdentityProvider\Service\Session\ConstraintFlashBagHandler;
use Coddin\IdentityProvider\Service\Symfony\RedirectResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\Handler\UserRegistrationHandler
 * @covers ::__construct
 */
final class UserRegistrationHandlerTest extends TestCase
{
    /** @var ConstraintFlashBagHandler & MockObject */
    private $constraintFlashBagHandler;
    /** @var RedirectResponseFactory & MockObject */
    private $redirectResponseFactory;
    /** @var RouterInterface & MockObject $router */
    private $router;

    protected function setUp(): void
    {
        $this->constraintFlashBagHandler = $this->createMock(ConstraintFlashBagHandler::class);
        $this->redirectResponseFactory = $this->createMock(RedirectResponseFactory::class);
        $this->router = $this->createMock(RouterInterface::class);
    }

    /**
     * @test
     * @covers ::resolve
     */
    public function resolve(): void
    {
        $requestConstraintException = $this->createMock(RequestConstraintException::class);

        $this->constraintFlashBagHandler
            ->expects(self::once())
            ->method('addMessagesFromException')
            ->with($requestConstraintException);

        $this->router
            ->expects(self::once())
            ->method('generate')
            ->with('coddin_identity_provider.register')
            ->willReturn('https://foo.bar');

        $redirectResponse = $this->createMock(RedirectResponse::class);

        $this->redirectResponseFactory
            ->expects(self::once())
            ->method('create')
            ->with('https://foo.bar')
            ->willReturn($redirectResponse);

        $exceptionEvent = $this->createMock(ExceptionEvent::class);
        $exceptionEvent
            ->expects(self::once())
            ->method('setResponse')
            ->with($redirectResponse);

        $handler = new UserRegistrationHandler(
            constraintFlashBagHandler: $this->constraintFlashBagHandler,
            redirectResponseFactory: $this->redirectResponseFactory,
            router: $this->router,
        );
        $handler->resolve(
            event: $exceptionEvent,
            requestConstraintException: $requestConstraintException,
        );
    }
}
