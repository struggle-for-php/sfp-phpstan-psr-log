<?php

declare(strict_types=1);

/**
 * @phpstan-param array{ok: string}|array{alsoOk: string, otherOk: string} $constantContext
 * @phpstan-param array{ok: string}|array{alsoOk: string, 123: string} $badKeyContext
 */
function main(Psr\Log\LoggerInterface $logger, array $nonTypedArray, array $constantContext, array $badKeyContext): void
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
    $logger->info('foo', [12345678901234567890 => "a"]);
    $logger->info('foo', ["a"]);
    $logger->log('info', 'foo', ["a"]);

    $okArray = ['ok' => __LINE__];
    $ngArray = [1 => __LINE__];
    $logger->info('ok', $okArray);
    $logger->info('ng', $ngArray); //ng

    $logger->info('union', $constantContext);
    $logger->info('union', $badKeyContext); // ng (when treatPhpDocTypesAsCertain on)
}
