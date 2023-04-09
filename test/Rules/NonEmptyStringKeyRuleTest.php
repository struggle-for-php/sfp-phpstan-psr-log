<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\NonEmptyStringKeyRule;

/**
 * @implements RuleTestCase<NonEmptyStringKeyRule>
 */
final class NonEmptyStringKeyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NonEmptyStringKeyRule();
    }

    /** @test */
    public function testProcessNode()
    {
        $this->analyse([__DIR__ . '/data/nonEmptyStringKey.php'], [
            'empty string'  => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() , key should be non empty string.',
                8,
            ],
            'integer'       => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() , key should be non empty string.',
                9,
            ],
            'DNumber'       => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() , key should be non empty string.',
                10,
            ],
            'not specified' => [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info() , key should be non empty string.',
                11,
            ],
        ]);
    }
}
