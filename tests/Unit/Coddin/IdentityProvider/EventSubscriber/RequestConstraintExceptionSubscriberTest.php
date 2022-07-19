<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\EventSubscriber;

use Coddin\IdentityProvider\EventSubscriber\RequestConstraintExceptionSubscriber;
use Coddin\IdentityProvider\Request\Handler\ResetPasswordRequestHandler;
use Coddin\IdentityProvider\Request\Handler\UserRegistrationHandler;
use Coddin\IdentityProvider\Request\ResetPasswordRequest;
use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\EventSubscriber\RequestConstraintExceptionSubscriber
 * @covers ::__construct
 */
final class RequestConstraintExceptionSubscriberTest extends TestCase
{
    /** @var ExceptionEvent & MockObject $exceptionEvent */
    private $exceptionEvent;
    /** @var UserRegistrationHandler & MockObject $userRegistrationHandler */
    private $userRegistrationHandler;
    /** @var ResetPasswordRequestHandler & MockObject */
    private $resetPasswordRequestHandler;

    protected function setUp(): void
    {
        $this->exceptionEvent = $this->createMock(ExceptionEvent::class);
        $this->userRegistrationHandler = $this->createMock(UserRegistrationHandler::class);
        $this->resetPasswordRequestHandler = $this->createMock(ResetPasswordRequestHandler::class);
    }

    /**
     * @test
     * @covers ::process
     */
    public function process_incorrect_exception(): void
    {
        $this->exceptionEvent
            ->expects(self::once())
            ->method('getThrowable')
            ->willReturn(new \Exception());

        $this->userRegistrationHandler
            ->expects(self::never())
            ->method('resolve');

        $subscriber = $this->createSubscriber();
        $subscriber->process($this->exceptionEvent);
    }

    /**
     * @test
     * @covers ::process
     */
    public function process_unknown_constraint_type(): void
    {
        $requestConstraintException = $this->createMock(RequestConstraintException::class);
        $requestConstraintException
            ->expects(self::once())
            ->method('getConstraintSubject')
            ->willReturn('Unknown\\Class');

        $this->exceptionEvent
            ->expects(self::once())
            ->method('getThrowable')
            ->willReturn($requestConstraintException);

        $this->userRegistrationHandler
            ->expects(self::never())
            ->method('resolve');

        self::expectException(\LogicException::class);

        $subscriber = $this->createSubscriber();
        $subscriber->process($this->exceptionEvent);
    }

    /**
     * @param class-string $constraintEvent
     * @test
     * @covers ::process
     * @dataProvider processConstraintEvents
     */
    public function process(string $constraintEvent, string $handler): void
    {
        $requestConstraintException = $this->createMock(RequestConstraintException::class);
        $requestConstraintException
            ->expects(self::once())
            ->method('getConstraintSubject')
            ->willReturn($constraintEvent);

        $this->exceptionEvent
            ->expects(self::once())
            ->method('getThrowable')
            ->willReturn($requestConstraintException);

        /* @phpstan-ignore-next-line */
        $this->{$handler}
            ->expects(self::once())
            ->method('resolve')
            ->with($this->exceptionEvent, $requestConstraintException);

        $subscriber = $this->createSubscriber();
        $subscriber->process($this->exceptionEvent);
    }

    /**
     * @return array<int, array<int, class-string|string>>
     */
    public function processConstraintEvents(): array
    {
        return [
            [ UserRegistration::class, 'userRegistrationHandler' ],
            [ ResetPasswordRequest::class, 'resetPasswordRequestHandler' ],
        ];
    }

    private function createSubscriber(): RequestConstraintExceptionSubscriber
    {
        return new RequestConstraintExceptionSubscriber(
            userRegistrationHandler: $this->userRegistrationHandler,
            resetPasswordRequestHandler: $this->resetPasswordRequestHandler,
        );
    }
}
