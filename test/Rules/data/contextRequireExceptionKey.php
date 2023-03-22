<?php

function main(Psr\Log\LoggerInterface $logger): void
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

        // Todo - handle function return type
        $logger->log(determineLogLevel(), 'foo'); // currently ignore

        // ok
        $logger->critical("foo", ['exception' => $exception2]);
    } finally {
        // ok
        $logger->emergency('foo');
    }
}

// phpcs:disable
/** @return 'notice'|'warning'|'alert' */
function determineLogLevel(): string
{
    return 'alert';
}
