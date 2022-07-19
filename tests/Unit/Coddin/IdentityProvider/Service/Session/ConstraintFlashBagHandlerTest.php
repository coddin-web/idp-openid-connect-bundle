<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Service\Session;

use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Coddin\IdentityProvider\Service\Session\ConstraintFlashBagHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Service\Session\ConstraintFlashBagHandler
 * @covers ::__construct
 */
final class ConstraintFlashBagHandlerTest extends TestCase
{
    /** @var RequestStack & MockObject */
    private $requestStack;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
    }

    /**
     * @test
     * @covers ::addMessagesFromException
     */
    public function add_messages_from_exception(): void
    {
        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);
        $constraintViolation
            ->expects(self::once())
            ->method('getMessage')
            ->willReturn('This is a message');

        $requestConstraintException = $this->createMock(RequestConstraintException::class);
        $requestConstraintException
            ->expects(self::once())
            ->method('getConstraintViolationList')
            ->willReturn(new ConstraintViolationList([$constraintViolation]));

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag
            ->expects(self::once())
            ->method('add')
            ->with('user_registration.error', 'This is a message');

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $this->requestStack
            ->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        $constraintFlashBagHandler = new ConstraintFlashBagHandler($this->requestStack);
        $constraintFlashBagHandler->addMessagesFromException($requestConstraintException);
    }
}
