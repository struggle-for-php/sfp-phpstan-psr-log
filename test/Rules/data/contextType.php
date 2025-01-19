<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\ContextType;

use Psr\Log\LoggerInterface;
use Throwable;

function main(
    LoggerInterface $logger,
    Throwable $throwable
): void {
    $logger->info('info', ['exception' => $throwable->getMessage()]);
}
