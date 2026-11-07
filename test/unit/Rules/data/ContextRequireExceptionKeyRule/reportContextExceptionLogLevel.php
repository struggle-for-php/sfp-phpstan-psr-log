<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

function main(LoggerInterface $logger): void
{
    try {
        $logger->notice("foo");

        throw new InvalidArgumentException();
    } catch (LogicException $exception) {
        $logger->debug("foo");
        $logger->info("foo");

        $context = ['foo' => 'FOO', 'bar' => 'BAR']; // to check offset variable
        $logger->debug('foo', $context); // would not be reported < info
    }
}
