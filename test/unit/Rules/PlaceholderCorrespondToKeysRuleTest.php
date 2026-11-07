<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\PlaceholderCorrespondToKeysRule;

/**
 * @extends RuleTestCase<PlaceholderCorrespondToKeysRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\PlaceholderCorrespondToKeysRule
 */
final class PlaceholderCorrespondToKeysRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PlaceholderCorrespondToKeysRule();
    }

    /** @test */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/placeholderCorrespondToKeys.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() is required, when placeholder braces exists - {nonContext}',
                23, // missing context
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::%s() is empty, when placeholder braces exists',
                24, // empty array
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has placeholder braces, but context key is not found against them. - {notMatched}',
                25, // notMatched
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has placeholder braces, but context key is not found against them. - {notMatched1},{notMatched2}',
                26, // many placeholders
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::log() has placeholder braces, but context key is not found against them. - {notMatched}',
                27, // log method
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has placeholder braces, but context key is not found against them. - {valid}',
                32, // union parameter
            ],
        ]);
    }
}
