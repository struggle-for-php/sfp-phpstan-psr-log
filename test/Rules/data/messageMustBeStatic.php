<?php

declare(strict_types=1);

const FOO = 'foo';

/**
 * @phpstan-param 'literal-a'|'literal-b' $literals
 * @phpstan-param 'literal-a'|non-empty-lowercase-string $literalOr
 */
function main(Psr\Log\LoggerInterface $logger, string $m, string $literals, string $literalOr): void
{
    // valid
    $logger->info('message is valid');

    $logger->info($m);
    $logger->info("Message contains {$m} variable");
    $logger->info("Message contains $m variable");
    $logger->info("Message contains " . $m . " variable");
    $logger->info('Message contains ' . $m . ' variable');
    $logger->info(sprintf('Message contains %s variable', $m));

    $logger->log('info', $m);

    $logger->info($literalOr);

    // Allow const
    $logger->info(FOO);

    // Allow assign
    $logger->info($ret = 'Invalid Request happened!');
    echo $ret;

    // Allow literal-string intersection
    $logger->info($literals);
}

/**
 * @param literal-string $literal
 */
function logging(Psr\Log\LoggerInterface $logger, string $literal): void
{
	$logger->info($literal);
}
