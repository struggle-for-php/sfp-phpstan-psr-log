<?php

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextRequireExceptionKeyRule;

/**
 * @implements RuleTestCase<ContextRequireExceptionKeyRule>
 */
class ContextRequireExceptionKeyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContextRequireExceptionKeyRule();
    }

    /** @test */
    public function testProcessNode()
    {
        $this->analyse([__DIR__ . '/data/contextRequireExceptionKey.php'], [
            'missing context'               => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() requires \'exception\' key. Current scope has Throwable variable - $exception',
                15,
            ],
            'invalid key'                   => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() requires \'exception\' key. Current scope has Throwable variable - $exception',
                16,
            ],
            'missing context - log method'  => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log() requires \'exception\' key. Current scope has Throwable variable - $exception',
                19,
            ],
            'invalid key - log method'      => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log() requires \'exception\' key. Current scope has Throwable variable - $exception',
                20,
            ],
            'missing context - other catch' => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::critical() requires \'exception\' key. Current scope has Throwable variable - $exception2',
                26,
            ],
            'invalid key - other catch'     => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log() requires \'exception\' key. Current scope has Throwable variable - $exception2',
                27,
            ],
        ]);
    }
}
