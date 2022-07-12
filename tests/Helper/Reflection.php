<?php

declare(strict_types=1);

namespace Tests\Helper;

final class Reflection
{
    /**
     * @param class-string $class
     * @return array<int, string>
     * @throws \ReflectionException
     */
    public static function getAllGetMethodsForClass(string $class): array
    {
        $reflection = new \ReflectionClass($class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        return \array_map(
            function (\ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->getName();
            },
            \array_filter(
                $methods,
                fn(\ReflectionMethod $reflectionMethod): bool => $reflectionMethod->class === $class && str_starts_with($reflectionMethod->getName(), 'get'),
            ),
        );
    }
}
