<?php

declare(strict_types=1);

namespace src;

use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

class Example
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function exceptionKeyOnlyAllowThrowable(Throwable $throwable): void
    {
        // invalid
        $this->logger->notice('foo', ['exception' => $throwable->getMessage()]);
        $this->logger->log('panic', 'foo', ['exception' => $throwable]);

        // valid
        $this->logger->log('notice', 'foo', ['exception' => $throwable]);
    }

    public function mustIncludesCurrentScopeThrowableIntoContext(Throwable $throwable): void
    {
        // Parameter $context of logger method Psr\Log\LoggerInterface::info() requires 'exception' key. Current scope has Throwable variable - $throwable
        $this->logger->notice('foo');

        $this->logger->notice('foo', ['user' => 1]);
    }

    public function reportContextExceptionLogLevel(Throwable $throwable): void
    {
        // phpstan.neon sfpPsrLog.reportContextExceptionLogLevel is 'notice'
        // so bellow would not report.
        $this->logger->debug('foo');
    }

    public function contextKeyNonEmptyString(): void
    {
        $this->logger->debug('foo', ['bar']);
    }

    public function contextKeyOriginalPattern(): void
    {
        $this->logger->debug('foo', ['first.second' => 'bar']);
    }

    public function placeholderCharacters(): void
    {
        $this->logger->info('message has {{doubleBrace}} .', ['doubleBrace' => 'bar']);
        $this->logger->info('message has { space } .', [' space ' => 'bar']);
    }

    public function placeholderCorrespondToKeys(): void
    {
        $this->logger->info('message has {nonContext} .');
        $this->logger->info('message has {empty} .', []);
        $this->logger->info('message has {notMatched} .', ['foo' => 'bar']);
        $this->logger->info('message has {notMatched1} , {matched1} , {notMatched2} .', ['matched1' => 'bar']);
        $this->logger->log('info', 'message has {notMatched} .', ['foo' => 'bar']);
    }

    public function messageNotStaticString(string $var): void
    {
        $this->logger->info("Message contains {$var} variable");
        $this->logger->info("Message contains $var variable");
        $this->logger->info("Message contains " . $var . " variable");
        $this->logger->info('Message contains ' . $var . ' variable');
        $this->logger->info(sprintf('Message contains %s variable', $var));
        $this->logger->log('info', sprintf('Message contains %s variable', $var));
    }
}
