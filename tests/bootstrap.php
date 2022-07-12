<?php

// phpcs:disable

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env.test.local')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env.test.local');
} else {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env.test');
}

exec(__DIR__ . '/bin/console doctrine:schema:drop --full-database --force --env=test --no-interaction');
exec(__DIR__ . '/bin/console doctrine:schema:update --force --env=test --no-interaction');
exec(__DIR__ . '/bin/console doctrine:fixtures:load --env=test --no-interaction');
