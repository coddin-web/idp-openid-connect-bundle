<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS)]
final class RequestValidation
{
}
