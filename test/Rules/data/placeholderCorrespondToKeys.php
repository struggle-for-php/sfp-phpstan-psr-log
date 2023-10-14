<?php

declare(strict_types=1);

function main(Psr\Log\LoggerInterface $logger, string $m): void
{
    // ignore
    $logger->info('foo', ['ok' => "a"]);
    $logger->info($m, ['ok' => "a"]);
    // valid
    $logger->info('message is {valid1_.} ..', ['valid1_.' => 'OK']);
    $logger->info('message is {valid2_.} ..', ['valid1_.' => 'OK', 'valid2_.' => 'OK']);
    // ignore - should be checked by PlaceHolderCharactersRule
    $logger->info('message has { invalid placeholder} .');
    $logger->info('message has { invalid placeholder} .', [' invalid placeholder' => 'bar']);

    $logger->info('message has {nonContext} .');
    $logger->info('message has {empty} .', []);
    $logger->info('message has {notMatched} .', ['foo' => 'bar']);
    $logger->info('message has {notMatched1} , {matched1} , {notMatched2} .', ['matched1' => 'bar']);
    $logger->log('info', 'message has {notMatched} .', ['foo' => 'bar']);
}
