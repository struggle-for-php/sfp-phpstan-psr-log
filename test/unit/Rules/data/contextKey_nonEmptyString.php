<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

/**
 * @phpstan-param array{ok: string}|array{alsoOk: string, otherOk: string} $constantContext
 * @phpstan-param array{ok: string}|array{alsoOk: string, 123: string} $badKeyContext
 */
function main(LoggerInterface $logger, array $nonTypedArray, array $constantContext, array $badKeyContext): void
{
    // ok
    $logger->info('foo', ['ok' => "a"]);
    $logger->log('info', 'foo', ['ok' => "a"]);
    // ok - without context - should be ignored
    $logger->log('info', 'ok');
    // ok
    $logger->info('non typed array', $nonTypedArray);

    // NG - for non-empty-string
    $logger->info('foo', ['' => "a"]);
    $logger->info('foo', [1 => "a"]);
    // DNumber key is tested in contextKey_nonEmptyString_floatKey.php
    $logger->info('foo', ["a"]);
    $logger->log('info', 'foo', ["a"]);

    $logger->info('union', $constantContext);
    $logger->info('union', $badKeyContext); // ng

    $okArray = ['ok' => __LINE__];
    $ngArray = [1 => __LINE__];
    $logger->info('ok', $okArray);
    $logger->info('ng', $ngArray); //ng

    // ok - without context - should be ignored
    $logger->info('foo');
}
