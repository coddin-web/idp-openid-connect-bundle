<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\Symfony;

use Symfony\Component\HttpFoundation\RedirectResponse;

final class RedirectResponseFactory
{
    /**
     * @param array<string, mixed> $headers
     */
    public function create(
        string $url,
        int $status = 302,
        array $headers = [],
    ): RedirectResponse {
        return new RedirectResponse(
            url: $url,
            status: $status,
            headers: $headers,
        );
    }
}
