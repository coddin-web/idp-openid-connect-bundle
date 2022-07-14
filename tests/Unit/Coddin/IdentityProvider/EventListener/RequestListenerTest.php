<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpMissingFieldTypeInspection
 */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\EventListener;

use Coddin\IdentityProvider\EventListener\UserLocaleListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\EventListener\UserLocaleListener
 */
final class RequestListenerTest extends TestCase
{
    /** @var RequestEvent & MockObject */
    private $requestEvent;
    /** @var Request & MockObject */
    private $request;
    /** @var SessionInterface & MockObject */
    private $session;
    /** @var MockObject & ServerBag */
    private $serverBag;

    public function setUp(): void
    {
        $this->requestEvent = $this->createMock(RequestEvent::class);
        $this->request = $this->createMock(Request::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->serverBag = $this->createMock(ServerBag::class);

        $this->request->server = $this->serverBag;
    }

    /**
     * @test
     * @covers ::onKernelRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function not_main_request(): void
    {
        $this->setupRequestEvent(false);

        $this->session
            ->expects(self::never())
            ->method('has')
            ->with('user_locale');

        $this->serverBag
            ->expects(self::never())
            ->method('getHeaders');

        $requestListener = new UserLocaleListener();
        $requestListener->onKernelRequest($this->requestEvent);
    }

    /**
     * @test
     * @covers ::onKernelRequest
     */
    public function incorrect_session(): void
    {
        $this->setupRequestEvent();

        $this->request
            ->expects(self::exactly(2))
            ->method('getSession')
            ->willReturn($this->session);

        $this->session
            ->expects(self::once())
            ->method('has')
            ->with('user_locale')
            ->willReturn(true);

        $this->session
            ->expects(self::once())
            ->method('get')
            ->with('user_locale')
            ->willReturn(true);

        self::expectException(\Exception::class);

        $requestListener = new UserLocaleListener();
        $requestListener->onKernelRequest($this->requestEvent);
    }

    /**
     * @test
     * @covers ::onKernelRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function set_user_locale(): void
    {
        $this->setupRequestEvent();

        $this->request
            ->expects(self::exactly(2))
            ->method('getSession')
            ->willReturn($this->session);

        $this->session
            ->expects(self::once())
            ->method('has')
            ->with('user_locale')
            ->willReturn(true);

        $this->session
            ->expects(self::once())
            ->method('get')
            ->with('user_locale')
            ->willReturn('nl');

        $this->request
            ->expects(self::once())
            ->method('setLocale')
            ->with('nl');

        $requestListener = new UserLocaleListener();
        $requestListener->onKernelRequest($this->requestEvent);
    }

    /**
     * @test
     * @covers ::onKernelRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function browser_locale_missing_header(): void
    {
        $this->setupRequestEvent();

        $this->request
            ->expects(self::once())
            ->method('getSession')
            ->willReturn($this->session);

        $this->session
            ->expects(self::once())
            ->method('has')
            ->with('user_locale')
            ->willReturn(false);

        $this->serverBag
            ->expects(self::once())
            ->method('getHeaders')
            ->willReturn(['ACCEPT__LANGUAGE' => 'incorrect_header_key']);

        $this->request
            ->expects(self::never())
            ->method('setLocale');

        $requestListener = new UserLocaleListener();
        $requestListener->onKernelRequest($this->requestEvent);
    }

    /**
     * @test
     * @covers ::onKernelRequest
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function browser_set_locale(): void
    {
        $this->setupRequestEvent();

        $this->request
            ->expects(self::once())
            ->method('getSession')
            ->willReturn($this->session);

        $this->session
            ->expects(self::once())
            ->method('has')
            ->with('user_locale')
            ->willReturn(false);

        $this->serverBag
            ->expects(self::once())
            ->method('getHeaders')
            ->willReturn(['ACCEPT_LANGUAGE' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7']);

        $this->request
            ->expects(self::once())
            ->method('setLocale')
            ->with('nl');

        $requestListener = new UserLocaleListener();
        $requestListener->onKernelRequest($this->requestEvent);
    }

    private function setupRequestEvent(bool $isMainRequest = true): void
    {
        if ($isMainRequest) {
            $this->requestEvent
                ->expects(self::once())
                ->method('getRequest')
                ->willReturn($this->request);
        }

        $this->requestEvent
            ->expects(self::once())
            ->method('isMainRequest')
            ->willReturn($isMainRequest);
    }
}
