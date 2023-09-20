<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\PlaceHolderInMessageRule;

/**
 * @extends RuleTestCase<PlaceHolderInMessageRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\PlaceHolderInMessageRule
 */
final class PlaceHolderInMessageRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PlaceHolderInMessageRule();
    }

    /**
     * @test
     */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/placeHolderInMessage.php'], [
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() should not includes double braces. - {{doubleBrace}}',
                15, // double braces
                'See https://www.php-fig.org/psr/psr-3/#12-message',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has braces. But it includes invalid characters for placeholder. - { space }',
                16, // invalid placeholder char
                'See https://www.php-fig.org/psr/psr-3/#12-message',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::log() has braces. But it includes invalid characters for placeholder. - {&invalid&}',
                17, // call log() method
                'See https://www.php-fig.org/psr/psr-3/#12-message',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() has braces. But it includes invalid characters for placeholder. - {&a},{&b},{&c}',
                18, // Many Invalid PlaceHolders
                'See https://www.php-fig.org/psr/psr-3/#12-message',
            ],
        ]);
    }
}
