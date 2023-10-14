<?php

declare(strict_types=1);

function main(Psr\Log\LoggerInterface $logger): void
{
    // ok
    $logger->info('foo', ['ok' => "a"]);
    $logger->log('info', 'foo', ['ok' => "a"]);
    // ok - without context - should be ignored
    $logger->log('info', 'ok');

    // NG - for non-empty-string
    $logger->info('foo', ['' => "a"]);
    $logger->info('foo', [1 => "a"]);
    $logger->info('foo', [12345678901234567890 => "a"]);
    $logger->info('foo', ["a"]);
    $logger->log('info', 'foo', ["a"]);
}
