<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\LogMethodLevelRule;

/**
 * @extends RuleTestCase<LogMethodLevelRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\LogMethodLevelRule
 */
final class LogMethodLevelRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new LogMethodLevelRule();
    }

    public function testLogMethodLevel(): void
    {
        $this->analyse([__DIR__ . '/data/logMethodLevel.php'], [
            [
                "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning', 'panic' given.",
                23,
            ],
            [
                "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning', string given.",
                24,
            ],
            [
                "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning', ('foo' | 'info' | 'panic') given.",
                25,
            ],
            [
                "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning', 100 given.",
                26,
            ],
        ]);
    }
}
