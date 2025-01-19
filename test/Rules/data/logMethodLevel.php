<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\ContextType;

use Psr\Log\LoggerInterface;

/**
 * @phpstan-param 'info'|'notice'|'debug' $validLevels
 * @phpstan-param 'info'|'foo'|'panic' $invalidLevels
 */
function main(
    LoggerInterface $logger,
    string $validLevels,
    string $unknownLevel,
    string $invalidLevels
): void {
    $logger->log('info', 'message');
    $logger->log($validLevels, 'message');

    // invalid
    $logger->log('panic', 'message');
    $logger->log($unknownLevel, 'message');
    $logger->log($invalidLevels, 'message');
}
