<?php


namespace SfpTest\PHPStan\Psr\Log\Asset;

use Psr\Log\LoggerInterface;

class LoggerCall
{
    public function logging(LoggerInterface $logger) : void
    {
        $logger->emergency("message", ['exception' => 'foo']);
        $logger->alert("message", ['exception' => 'foo']);
        $logger->critical("message", ['exception' => 'foo']);
        $logger->error("message", ['exception' => 'foo']);
        $logger->warning("message", ['exception' => 'foo']);
        $logger->notice("message", ['exception' => 'foo']);
        $logger->info("message", ['exception' => 'foo']);
        $logger->debug("message", ['exception' => 'foo']);
    }
}
