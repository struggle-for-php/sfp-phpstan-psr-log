<?php

/**
 * @var \Psr\Log\LoggerInterface $logger
 */

try {
    $logger->debug("foo"); // allow
    $logger->log('debug', "foo"); // allow

    throw new InvalidArgumentException();
} catch (LogicException $exception) {
    $logger->info("foo");
    $logger->info('foo', ['throwable' => $exception]);

    // log method
    $logger->log('notice', 'foo');
    $logger->log('notice', 'foo', ['throwable' => $exception]);

    // ok
    $logger->alert("foo", ['exception' => $exception]);
    $logger->log('alert', 'foo', ['exception' => $exception]);
} catch (RuntimeException|Throwable $exception2) {
    $logger->critical('foo');
    $logger->log('critical', 'foo');

    $logger->critical("foo", ['exception' => $exception2]);
} finally {
    $logger->emergency('foo');
}