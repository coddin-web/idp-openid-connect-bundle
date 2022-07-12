<?php

declare(strict_types=1);

namespace Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array<string, mixed> $variables
     */
    public static function assertVariableEqualsGetMethod(
        mixed $class,
        array $variables,
    ): void {
        foreach ($variables as $variableName => $variableValue) {
            $method = 'get' . \ucfirst($variableName);

            self::assertEquals(
                $variableValue,
                /* @phpstan-ignore-next-line */
                $class->{$method}(),
            );
        }
    }
}
