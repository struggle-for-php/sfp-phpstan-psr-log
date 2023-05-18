<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextRequireExceptionKeyRule;

/**
 * @implements RuleTestCase<ContextRequireExceptionKeyRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextRequireExceptionKeyRule
 */
final class ContextRequireExceptionKeyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContextRequireExceptionKeyRule('info');
    }

    /**
     * @test
     */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/contextRequireExceptionKey.php'], [
            'missing context'               => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() requires \'exception\' key. Current scope has Throwable variable - $exception',
                17,
            ],
            'invalid key'                   => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() requires \'exception\' key. Current scope has Throwable variable - $exception',
                18,
            ],
            'missing context - log method'  => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log() requires \'exception\' key. Current scope has Throwable variable - $exception',
                21,
            ],
            'invalid key - log method'      => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log() requires \'exception\' key. Current scope has Throwable variable - $exception',
                22,
            ],
            'missing context - other catch' => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::critical() requires \'exception\' key. Current scope has Throwable variable - $exception2',
                29,
            ],
            'invalid key - other catch'     => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log() requires \'exception\' key. Current scope has Throwable variable - $exception2',
                30,
            ],
            'empty array'                   => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::critical() requires \'exception\' key. Current scope has Throwable variable - $exception2',
                35,
            ],
            'context offset variable '      => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::critical() requires \'exception\' key. Current scope has Throwable variable - $exception2',
                37,
            ],
        ]);
    }
}
