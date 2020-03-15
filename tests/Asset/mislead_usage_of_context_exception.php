<?php

use Psr\Log\AbstractLogger;

$abstractLogger = new class extends AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
    }
};

$abstractLogger->emergency("message", ['exception' => 'foo']);
$abstractLogger->alert("message", ['exception' => 'foo']);
$abstractLogger->critical("message", ['exception' => 'foo']);
$abstractLogger->error("message", ['exception' => 'foo']);
$abstractLogger->warning("message", ['exception' => 'foo']);
$abstractLogger->notice("message", ['exception' => 'foo']);
$abstractLogger->info("message", ['exception' => 'foo']);
$abstractLogger->debug("message", ['exception' => 'foo']);
