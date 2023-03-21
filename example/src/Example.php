<?php

namespace src;

use Psr\Log\LoggerInterface;

class Example
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function exceptionKeyOnlyAllowThrowable(\Throwable $throwable): void
    {
        // invalid
        $this->logger->debug('foo', ['exception' => $throwable->getMessage()]);
        $this->logger->log('panic', 'foo', ['exception' => $throwable]);

        // valid
        $this->logger->log('info', 'foo', ['exception' => $throwable]);
    }

    public function mustIncludesCurrentScopeThrowableIntoContext(\Throwable $throwable)
    {
        // Parameter $context of logger method Psr\Log\LoggerInterface::info() requires 'exception' key. Current scope has Throwable variable - $throwable
        $this->logger->info('foo');
    }
}
