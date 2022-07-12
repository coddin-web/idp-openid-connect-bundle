<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\OpenIDConnect\Domain\Generator;

use Coddin\IdentityProvider\Generator\OAuthClientCreate;
use Coddin\IdentityProvider\DataFixtures\Data\OAuthClient;
use Tests\TestCase;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Generator\OAuthClientCreate
 */
final class OAuthClientCreateTest extends TestCase
{
    /**
     * @test
     * @covers ::create
     */
    public function create_the_oauthClient(): void
    {
        $oauthClient = OAuthClientCreate::create(
            externalId: OAuthClient::ExternalID->value,
            externalIdReadable: OAuthClient::ExternalIDReadable->value,
            name: OAuthClient::Name->value,
            displayName: OAuthClient::DisplayName->value,
            secret: OAuthClient::Secret->value,
            creationWebhookUrl: OAuthClient::WebhookUrl->value,
        );

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(\Coddin\IdentityProvider\Entity\OpenIDConnect\OAuthClient::class, $oauthClient);
        self::assertVariableEqualsGetMethod(
            class: $oauthClient,
            variables: [
                'externalId' => $oauthClient->getExternalId(),
                'externalIdReadable' => $oauthClient->getExternalIdReadable(),
                'name' => $oauthClient->getName(),
                'displayName' => $oauthClient->getDisplayName(),
                'secret' => $oauthClient->getSecret(),
                'creationWebhookUrl' => $oauthClient->getCreationWebhookUrl(),
            ],
        );
    }
}
