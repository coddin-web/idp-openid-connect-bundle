<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\EventSubscriber;

use Coddin\IdentityProvider\EventSubscriber\RequestConstraintExceptionSubscriber;
use Coddin\IdentityProvider\Request\Handler\UserRegistrationHandler;
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
    /** @var MockObject & ExceptionEvent $exceptionEvent */
    private $exceptionEvent;
    /** @var UserRegistrationHandler & MockObject $userRegistrationHandler */
    private $userRegistrationHandler;

    protected function setUp(): void
    {
        $this->exceptionEvent = $this->createMock(ExceptionEvent::class);
        $this->userRegistrationHandler = $this->createMock(UserRegistrationHandler::class);
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

        $subscriber = new RequestConstraintExceptionSubscriber($this->userRegistrationHandler);
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

        $subscriber = new RequestConstraintExceptionSubscriber($this->userRegistrationHandler);
        $subscriber->process($this->exceptionEvent);
    }

    /**
     * @param class-string $constraintEvent
     * @test
     * @covers ::process
     * @dataProvider processConstraintEvents
     */
    public function process(string $constraintEvent): void
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

        $this->userRegistrationHandler
            ->expects(self::once())
            ->method('resolve')
            ->with($this->exceptionEvent, $requestConstraintException);

        $subscriber = new RequestConstraintExceptionSubscriber($this->userRegistrationHandler);
        $subscriber->process($this->exceptionEvent);
    }

    /**
     * @return array<array<class-string>>
     */
    public function processConstraintEvents(): array
    {
        return [
            [ UserRegistration::class ],
        ];
    }
}
