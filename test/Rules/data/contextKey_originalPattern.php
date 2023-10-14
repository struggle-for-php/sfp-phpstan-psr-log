<?php

declare(strict_types=1);

function main(Psr\Log\LoggerInterface $logger): void
{
    // ok
    $logger->info('foo', ['ok' => "a"]);
    $logger->log('info', 'foo', ['ok' => "a"]);
    // ok - without context - should be ignored
    $logger->log('info', 'ok');

    // ng
    $logger->info('foo', ['a.b' => "a"]);
}
