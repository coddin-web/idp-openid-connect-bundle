<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Exception;

use Doctrine\ORM\EntityNotFoundException;

final class OAuthEntityNotFoundException extends EntityNotFoundException
{
    public static function fromClassNameAndId(
        string $className,
        int $id,
    ): self {
        return new self(
            self::createMessage($className, 'id', $id),
        );
    }

    public static function fromClassNameAndUuid(
        string $className,
        string $uuid,
    ): self {
        return new self(
            self::createMessage($className, 'uuid', $uuid),
        );
    }

    public static function fromClassNameAndExternalId(
        string $className,
        string $externalId,
    ): self {
        return new self(
            self::createMessage($className, 'externalId', $externalId),
        );
    }

    private static function createMessage(
        string $className,
        string $attribute,
        bool|float|int|string|null $value,
    ): string {
        return sprintf(
            'OAuthEntity of type `%s` with %s `%s` was not found',
            $className,
            $attribute,
            ($value ?? 'NULL'),
        );
    }
}
