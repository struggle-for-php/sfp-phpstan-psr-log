<?php

declare(strict_types=1);

/**
 * @phpstan-param array{exception: \Exception} $context
 * @phpstan-param array{exception: \Exception}|array{exception2: \Exception} $unionContext
 *
 * NOTES: union param would be reported level7(reportMaybes)
 * https://phpstan.org/r/8f1158f4-0cac-4ef9-9abe-29a892d502af
 *
 */
function main(
    Psr\Log\LoggerInterface $logger,
    array $nonTypedArray,
    array $context,
    array $unionContext
): void {
    try {
        throw new InvalidArgumentException();
    } catch (InvalidArgumentException $exception) {
        $logger->notice('foo', $nonTypedArray);
        $logger->notice('foo', $context);
        $logger->notice('foo', $unionContext);

        $logger->notice('foo', array_merge(['foo' => 1],  ['exception' => $exception] ));
        $logger->notice('foo', array_merge(['foo' => 1],  ['exception2' => $exception] ));
    }
}
