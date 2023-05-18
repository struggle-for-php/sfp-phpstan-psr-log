<?php

declare(strict_types=1);

use SfpTest\PHPStan\Psr\Log\Rules\OtherLoggerInterface;

function main(Psr\Log\LoggerInterface $logger, OtherLoggerInterface $otherLogger): void
{
    try {
        $logger->debug("foo");
        $logger->log('debug', "foo");

        throw new InvalidArgumentException();
    } catch (LogicException $exception) {
        // ng
        $logger->debug("foo"); // but would not be report
        $logger->info("foo");
        $logger->info('foo', ['throwable' => $exception]);

        // ng. (by `log` call)
        $logger->log('notice', 'foo');
        $logger->log('notice', 'foo', ['throwable' => $exception]);

        // ok
        $logger->alert("foo", ['exception' => $exception]);
        $logger->log('alert', 'foo', ['exception' => $exception]);
    } catch (RuntimeException | Throwable $exception2) {
        // ng
        $logger->critical('foo');
        $logger->log('critical', 'foo');
        $logger->debug("foo", ['exception' => new DateTimeImmutable()]); // but would not be report

        // ng
        $context = [];
        $logger->critical('foo', $context);
        $context = ['foo' => 'FOO', 'bar' => 'BAR']; // to check offset variable
        $logger->critical('foo', $context);
        // ok
        $context = ['foo' => 'bar', 'exception' => $exception2]; // to check offset variable
        $logger->critical('foo', $context);
        $context = ['exception' => $exception2];
        $logger->critical('foo', $context);

        // Todo - handle function return type
        $logger->log(determineLogLevel(), 'foo'); // currently ignore

        // ok
        $logger->critical("foo", ['exception' => $exception2]);
        $logger->critical("foo", ['foo' => 1, 'exception' => $exception2]);
    } finally {
        // ok
        $logger->emergency('foo');
    }

    // just for test coverage...
    $logger->critical();
    $logger->none('foo');
    $otherLogger->critical('message');
}

// phpcs:disable
/** @return 'notice'|'warning'|'alert' */
function determineLogLevel(): string
{
    return 'alert';
}

