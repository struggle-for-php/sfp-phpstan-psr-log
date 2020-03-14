<?php

use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;

$loggerImpl = new \SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl;

$abstractLogger = new class extends AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
    }
};


$loggerImpl->emergency("message", ['exception' => 'foo']);
$loggerImpl->alert("message", ['exception' => 'foo']);
$loggerImpl->critical("message", ['exception' => 'foo']);
$loggerImpl->error("message", ['exception' => 'foo']);
$loggerImpl->warning("message", ['exception' => 'foo']);
$loggerImpl->notice("message", ['exception' => 'foo']);
$loggerImpl->info("message", ['exception' => 'foo']);
$loggerImpl->debug("message", ['exception' => 'foo']);

$abstractLogger->emergency("message", ['exception' => 'foo']);
$abstractLogger->alert("message", ['exception' => 'foo']);
$abstractLogger->critical("message", ['exception' => 'foo']);
$abstractLogger->error("message", ['exception' => 'foo']);
$abstractLogger->warning("message", ['exception' => 'foo']);
$abstractLogger->notice("message", ['exception' => 'foo']);
$abstractLogger->info("message", ['exception' => 'foo']);
$abstractLogger->debug("message", ['exception' => 'foo']);
