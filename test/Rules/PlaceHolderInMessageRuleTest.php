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
            'double braces'            => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() should not includes double braces. - {{doubleBrace}}',
                15,
            ],
            'invalid placeholder char' => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has braces. But it includes invalid characters for placeholder. - { space }',
                16,
            ],
            'non space before braces'  => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has braces. There should be whitespace between placeholder and word.',
                17,
            ],
            'non space after braces'   => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has braces. There should be whitespace between placeholder and word.',
                18,
            ],
        ]);
    }
}
