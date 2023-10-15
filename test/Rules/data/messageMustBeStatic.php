<?php

declare(strict_types=1);

function main(Psr\Log\LoggerInterface $logger, string $m): void
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

    // Allow assign
    $logger->info($ret = 'Invalid Request happened!');
    echo $ret;
}
