<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DataFixtures\Data;

enum OAuthClient: string
{
    case ExternalID = '462be77ded6db5f2fa691d9f2b8d0be0';
    case ExternalIDReadable = 'company/client';
    case Name = 'OAuthClient';
    case DisplayName = 'DisplayName';
    case Secret = '$ecr3t';
    case SecretEncrypted = '$2y$11$VKCpoJ1bmu8rGVWzTBuVYuvngeIPf0gHGWASrrpIKEH8NUwmrYuS.';
    case WebhookUrl = 'https://foo.bar';
}
