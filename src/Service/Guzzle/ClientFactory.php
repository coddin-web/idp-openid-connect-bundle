<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

final class ClientFactory
{
    /**
     * @codeCoverageIgnore
     */
    public function create(): ClientInterface
    {
        return new Client();
    }
}
