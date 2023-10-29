<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule;

use function array_pop;

/**
 * @extends RuleTestCase<ContextKeyRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule
 */
final class ContextKeyRuleTest extends RuleTestCase
{
    /** @var bool */
    private $treatPhpDocTypesAsCertain = false;

    /** @var null|string */
    private $contextKeyOriginalPattern;

    protected function getRule(): Rule
    {
        return new ContextKeyRule($this->treatPhpDocTypesAsCertain, $this->contextKeyOriginalPattern);
    }

    /**
     * @dataProvider provideNonEmptyStringKeyPattern
     */
    public function testAlwaysShouldBeCheckedNonEmptyString(bool $treatPhpDocTypesAsCertain, array $expectedErrors): void
    {
        $this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
        $this->contextKeyOriginalPattern = null;
        $this->analyse([__DIR__ . '/data/contextKey_nonEmptyString.php'], $expectedErrors);
    }

    public static function provideNonEmptyStringKeyPattern(): array
    {
        $expectedErrors = [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                20, // empty string
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                21, // integer
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                22, // DNumber
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                23, // not specified
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log(), key should be non empty string.',
                24, // log method call
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                29, // inline array
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                32, // union parameter
            ],
        ];

        $expectedErrors1 = $expectedErrors;
        array_pop($expectedErrors);

        return [
            [
                'treatPhpDocTypesAsCertain' => true,
                $expectedErrors1,
            ],
            [
                'treatPhpDocTypesAsCertain' => false,
                $expectedErrors,
            ],
        ];
    }

    public function testWithPattern(): void
    {
        $this->contextKeyOriginalPattern = '#\A[A-Za-z0-9-]+\z#';
        $this->analyse([__DIR__ . '/data/contextKey_originalPattern.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be match #\A[A-Za-z0-9-]+\z#.',
                14,
            ],
        ]);
    }

    public function testWithBadRegex(): void
    {
        $this->contextKeyOriginalPattern = '#\A[A-Za-z0-9-]+';
        $this->analyse([__DIR__ . '/data/contextKey_originalPattern.php'], [
            [
                'Your contextKeyOriginalPattern #\A[A-Za-z0-9-]+ seems not valid regex. Failed.',
                8,
            ],
            [
                'Your contextKeyOriginalPattern #\A[A-Za-z0-9-]+ seems not valid regex. Failed.',
                9,
            ],
            [
                'Your contextKeyOriginalPattern #\A[A-Za-z0-9-]+ seems not valid regex. Failed.',
                14,
            ],
        ]);
    }
}
