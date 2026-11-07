<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

function main(LoggerInterface $logger): void
{
    // NG - for non-empty-string
    // DNumber
    $logger->info('foo', [12345678901234567890 => "a"]);
}
