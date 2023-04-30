<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\PlaceHolderInMessageRule;

/**
 * @implements RuleTestCase<PlaceHolderInMessageRule>
 */
final class PlaceHolderInMessageRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PlaceHolderInMessageRule();
    }

    /** @test */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/placeHolderInMessage.php'], [
            'double braces'             => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() should not includes double braces. - {{doubleBrace}}',
                15,
            ],
            'invalid placeholder char'  => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has braces. But it includes invalid characters for placeholder. - { space }',
                16,
            ],
            'call log() method'         => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::log() has braces. But it includes invalid characters for placeholder. - {&invalid&}',
                17,
            ],
            'Many Invalid PlaceHolders' => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has braces. But it includes invalid characters for placeholder. - {&a},{&b},{&c}',
                18,
            ],
        ]);
    }
}
