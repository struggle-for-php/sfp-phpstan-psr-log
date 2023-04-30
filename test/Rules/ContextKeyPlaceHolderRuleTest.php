<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextKeyPlaceHolderRule;

/**
 * @implements RuleTestCase<ContextKeyPlaceHolderRule>
 */
final class ContextKeyPlaceHolderRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContextKeyPlaceHolderRule();
    }

    /** @test */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/contextKeyPlaceHolder.php'], [
            'missing context'   => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() is required, when placeholder braces exists - {nonContext}',
                17,
            ],
            'empty array'       => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has placeholder braces, but context key is not found against them. - {empty}',
                18,
            ],
            'notMatched'        => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has placeholder braces, but context key is not found against them. - {notMatched}',
                19,
            ],
            'many placeholders' => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has placeholder braces, but context key is not found against them. - {notMatched1},{notMatched2}',
                20,
            ],
            'log method'        => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::log() has placeholder braces, but context key is not found against them. - {notMatched}',
                21,
            ],
        ]);
    }
}
