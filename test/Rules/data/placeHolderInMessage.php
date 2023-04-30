<?php

declare(strict_types=1);

function main(Psr\Log\LoggerInterface $logger, string $m): void
{
    // ignore
    $logger->info('foo', ['ok' => "a"]);
    $logger->info($m, ['ok' => "a"]);
    // valid
    $logger->info('message is {valid1_.} ..', ['valid' => 'OK']);
    $logger->info('message is {valid1_.}.', ['valid' => 'OK']);
    $logger->info('メッセージは{valid1_.}です', ['valid' => 'OK']);

    $logger->info('message has {{doubleBrace}} .', ['doubleBrace' => 'bar']);
    $logger->info('message has { space } .', [' space ' => 'bar']);
    $logger->info('message has prev{foo} .', ['foo' => 'bar']);
    $logger->info('message has {foo}back ..', ['foo' => 'bar']);
}
