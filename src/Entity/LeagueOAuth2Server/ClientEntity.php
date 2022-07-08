<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Entity\LeagueOAuth2Server;

use Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthRedirectUri;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

final class ClientEntity implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    // @phpstan-ignore-next-line
    private bool $isPkce;

    /**
     * @codeCoverageIgnore
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param OAuthRedirectUri|array<int, OAuthRedirectUri> $redirectUri
     */
    public function setRedirectUri(OAuthRedirectUri|array $redirectUri): void
    {
        if ($redirectUri instanceof OAuthRedirectUri) {
            $this->redirectUri = $redirectUri->getUri();

            return;
        }

        if (\count($redirectUri) === 1) {
            $this->redirectUri = $redirectUri[0]->getUri();
        } else {
            $redirectUris = [];
            foreach ($redirectUri as $uri) {
                $redirectUris[] = $uri->getUri();
            }
            $this->redirectUri = $redirectUris;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function setConfidential(bool $isConfidential): void
    {
        $this->isConfidential = $isConfidential;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setIsPkce(bool $isPkce): void
    {
        $this->isPkce = $isPkce;
    }
}
