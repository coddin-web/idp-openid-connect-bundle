<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

final class UserLocaleListener
{
    /**
     * @throws \Exception
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->getSession()->has('user_locale')) {
            $locale = $request->getSession()->get('user_locale');

            if (!is_string($locale)) {
                throw new \Exception('Locale set in the Session must be of type string');
            }

            $request->setLocale($locale);

            return;
        }

        $serverHeaders = $request->server->getHeaders();
        if (!isset($serverHeaders['ACCEPT_LANGUAGE'])) {
            return;
        }

        $serverLocale = $serverHeaders['ACCEPT_LANGUAGE'];
        $locale = substr($serverLocale, 0, 2);
        if ($locale !== '') {
            $request->setLocale($locale);
        }
    }
}
