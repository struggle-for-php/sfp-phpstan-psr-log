<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\ContextType;

use Psr\Log\LoggerInterface;

/**
 * @phpstan-param array{exception: string} $context1
 * @phpstan-param array{exception: string}|array{exception: Throwable} $context2
 */
function main(
    LoggerInterface $logger,
    array $context1,
    array $context2
): void {
    $logger->info('info', $context1);
    $logger->info('info', $context2);
}
