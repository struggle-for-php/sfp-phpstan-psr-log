<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextKeyNonEmptyStringRule;

/**
 * @extends RuleTestCase<ContextKeyNonEmptyStringRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextKeyNonEmptyStringRule
 */
final class ContextKeyNonEmptyStringRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContextKeyNonEmptyStringRule();
    }

    /** @test */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/contextKeyNonEmptyStringRule.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                8, // empty string
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                9, // integer
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                10, // DNumber
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                11, // not specified
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log(), key should be non empty string.',
                14, // log method call
            ],
        ]);
    }
}
