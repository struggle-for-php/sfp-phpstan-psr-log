<?php

namespace SfpTest\PHPStan\Psr\Log;

class ThrowableStubTest extends AbstractTest
{
    public function provideExpectErrors() : array
    {
        return [
            [<<<'EXPECT'
Parameter #2 $context of method Psr\Log\LoggerInterface::emergency() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::alert() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::critical() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::error() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::warning() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::notice() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::debug() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::emergency() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::alert() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::critical() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::error() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::warning() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::notice() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::info() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::debug() expects array()|array('exception' => Throwable), array('exception' => 'foo') given.

EXPECT]
]
            ;
    }


    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../throwable-extension.neon'
        ];
    }
}