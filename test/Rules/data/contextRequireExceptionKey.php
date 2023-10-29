<?php

declare(strict_types=1);

use SfpTest\PHPStan\Psr\Log\Rules\OtherLoggerInterface;

/**
 * @phpstan-param 'debug'|'info'|'notice' $logLevels
 * @phpstan-param 'none'|'foo' $badLogLevels
 */
function main(
    Psr\Log\LoggerInterface $logger,
    OtherLoggerInterface $otherLogger,
    array $logLevels,
    array $badLogLevels
): void {
    try {
        $logger->debug("foo"); // ok - this line's scope does not have Throwable
        $logger->log('debug', "foo"); // also ok (by `log()` call)

        throw new InvalidArgumentException();
    } catch (InvalidArgumentException $exception) {
        // ng - without 'exception' key
        $logger->notice('foo');  // missing context
        $logger->notice('foo', ['throwable' => $exception]); // invalid key

        // also ng (by `log()` call)
        $logger->log('notice', 'foo');
        $logger->log('notice', 'foo', ['throwable' => $exception]); // invalid key - log method

        // ng - array variable passed pattern
        $context = [];
        $logger->notice('foo', $context); // empty array
        $context = ['foo' => 'FOO', 'bar' => 'BAR']; // to check offset variable
        $logger->notice('foo', $context);
        // ng - array merge, bad key
        $logger->notice('foo', array_merge(['foo' => 1], ['exception2' => $exception]));
        $logger->notice('foo', ['foo' => 1] + ['exception2' => $exception]);  // after array plus

        // OK
        $logger->notice('foo', ['exception' => $exception]);
        $logger->log('notice', 'foo', ['exception' => $exception]);
        // OK - array variable passed pattern
        $context = ['foo' => 'bar', 'exception' => $exception]; // to check offset variable
        $logger->notice('foo', $context);
        $context = ['exception' => $exception];
        $logger->notice('foo', $context);
        // OK - array merge
        $logger->notice('foo', array_merge(['foo' => 1], ['exception' => $exception]));
        $logger->notice('foo', ['foo' => 1] + ['exception' => $exception]);
    } catch (RuntimeException | Throwable $exception2) {
        // also ng - when union exception (other catch)
        $logger->notice('foo');
        $logger->log('notice', 'foo');
        $logger->notice("foo", ['exception' => new DateTimeImmutable()]); // would be checked by array-shapes
        // ok
        $logger->notice("foo", ['exception' => $exception2]);
        $logger->notice("foo", ['foo' => 1, 'exception' => $exception2]);

        // Todo - handle function return type
        $logger->log(determineLogLevel(), 'foo');
        $logger->critical('foo', returnMixedArray());
        $logger->critical('foo', returnExceptionHasArray()); // returnExceptionHasArray would be ErrorType, should not be reported
    } finally {
        // ok - when finally
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

/** @return array<mixed> */
function returnMixedArray() : array
{
    return ['exception' => new stdClass()];
}

/** @return array{exception: \Throwable} */
function returnExceptionHasArray() : array
{
    return ['exception' => new \Exception];
}