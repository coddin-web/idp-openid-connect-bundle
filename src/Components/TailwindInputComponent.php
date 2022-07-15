<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'idp_openid_connect.input',
    template: '@CoddinIdentityProvider/components/input.html.twig',
)]
final class TailwindInputComponent
{
    public string $name;
    public string $type = 'text';
    public bool $autofocus = false;
    public string $message;
    public string $helpText = '';
}
