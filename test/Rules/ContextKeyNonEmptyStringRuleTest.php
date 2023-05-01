<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextKeyNonEmptyStringRule;

/**
 * @implements RuleTestCase<ContextKeyNonEmptyStringRule>
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
            'empty string'  => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                8,
            ],
            'integer'       => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                9,
            ],
            'DNumber'       => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                10,
            ],
            'not specified' => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                11,
            ],
        ]);
    }
}
