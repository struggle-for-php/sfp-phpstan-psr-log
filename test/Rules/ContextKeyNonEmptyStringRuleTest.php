<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule;

/**
 * @extends RuleTestCase<ContextKeyRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule
 */
final class ContextKeyNonEmptyStringRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContextKeyRule();
    }

    /** @test */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/contextKey_nonEmptyString.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                14, // empty string
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                15, // integer
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                16, // DNumber
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                17, // not specified
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log(), key should be non empty string.',
                18, // log method call
            ],
        ]);
    }
}
