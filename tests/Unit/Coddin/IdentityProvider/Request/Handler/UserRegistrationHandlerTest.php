<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Handler;

use Coddin\IdentityProvider\Request\Handler\UserRegistrationHandler;
use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\Handler\UserRegistrationHandler
 * @covers ::__construct
 */
final class UserRegistrationHandlerTest extends TestCase
{
    /** @var MockObject & RequestStack $requestStack */
    private MockObject|RequestStack $requestStack;
    /** @var RouterInterface & MockObject $router */
    private RouterInterface|MockObject $router;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->router = $this->createMock(RouterInterface::class);
    }

    /**
     * @test
     * @covers ::resolve
     */
    public function resolve(): void
    {
        $flashBag = $this->createMock(FlashBag::class);
        $flashBag
            ->expects(self::once())
            ->method('add')
            ->with(UserRegistration::FLASH_BAG_ERROR_TYPE, 'The constraint message');

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $this->requestStack
            ->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        $exceptionEvent = $this->createMock(ExceptionEvent::class);
        $exceptionEvent
            ->expects(self::once())
            ->method('setResponse');

        $constraintViolation = $this->createMock(ConstraintViolation::class);
        $constraintViolation
            ->expects(self::once())
            ->method('getMessage')
            ->willReturn('The constraint message');

        $requestConstraintException = $this->createMock(RequestConstraintException::class);
        $requestConstraintException
            ->expects(self::once())
            ->method('getConstraintViolationList')
            ->willReturn(new ConstraintViolationList([$constraintViolation]));

        $this->router
            ->expects(self::once())
            ->method('generate')
            ->with('coddin_identity_provider.register')
            ->willReturn('https://foo.bar');

        $handler = new UserRegistrationHandler(
            requestStack: $this->requestStack,
            router: $this->router,
        );
        $handler->resolve(
            event: $exceptionEvent,
            requestConstraintException: $requestConstraintException,
        );
    }
}
