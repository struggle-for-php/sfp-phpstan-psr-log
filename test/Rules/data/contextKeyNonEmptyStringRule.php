<?php

declare(strict_types=1);

function main(Psr\Log\LoggerInterface $logger): void
{
    $logger->info('foo', ['ok' => "a"]);
    $logger->info('foo', ['' => "a"]);
    $logger->info('foo', [1 => "a"]);
    $logger->info('foo', [12345678901234567890 => "a"]);
    $logger->info('foo', ["a"]);
}
